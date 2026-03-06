<?php

declare(strict_types=1);

namespace App\Traits\Billing;

use App\Enums\Billing\CreditTransactionType;
use App\Events\Billing\CreditsAdded;
use App\Events\Billing\CreditsDeducted;
use App\Models\Billing\Credit;
use App\Models\Billing\CreditPack;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

trait HasCredits
{
    /**
     * Current credit balance (sum of non-expired positive running_balance from latest per-creditable ledger).
     */
    public function creditBalance(): int
    {
        $latest = Credit::query()
            ->where('organization_id', $this->getAttribute('id'))
            ->where('creditable_type', $this->getMorphClass())
            ->where('creditable_id', $this->getKey())
            ->orderByDesc('id')
            ->first();

        return $latest ? $latest->running_balance : 0;
    }

    /**
     * Sum of non-expired credit amounts (simplified: sum positive credits minus usage).
     */
    public function availableCredits(): int
    {
        return (int) Credit::query()
            ->where('organization_id', $this->getAttribute('id'))
            ->where('creditable_type', $this->getMorphClass())
            ->where('creditable_id', $this->getKey())
            ->where(function ($q): void {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->where('amount', '>', 0)
            ->sum('amount')
            - (int) Credit::query()
                ->where('organization_id', $this->getAttribute('id'))
                ->where('creditable_type', $this->getMorphClass())
                ->where('creditable_id', $this->getKey())
                ->where('amount', '<', 0)
                ->sum(DB::raw('ABS(amount)'));
    }

    /**
     * Credit ledger entries for this creditable entity.
     */
    public function credits(): MorphMany
    {
        return $this->morphMany(Credit::class, 'creditable');
    }

    /**
     * Expire credits that have passed their expiration date. Returns total amount expired.
     */
    public function expireCredits(): int
    {
        $expiredCredits = Credit::query()
            ->where('organization_id', $this->getAttribute('id'))
            ->where('creditable_type', $this->getMorphClass())
            ->where('creditable_id', $this->getKey())
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->where('amount', '>', 0)
            ->get();

        if ($expiredCredits->isEmpty()) {
            return 0;
        }

        $totalExpired = 0;
        $currentBalance = $this->creditBalance();

        foreach ($expiredCredits as $credit) {
            $expiredAmount = (int) $credit->amount;
            $totalExpired += $expiredAmount;
            $newBalance = $currentBalance - $expiredAmount;

            Credit::query()->create([
                'organization_id' => $this->getAttribute('id'),
                'creditable_type' => $this->getMorphClass(),
                'creditable_id' => $this->getKey(),
                'amount' => -$expiredAmount,
                'running_balance' => $newBalance,
                'type' => CreditTransactionType::Expiry,
                'description' => 'Credits expired',
                'metadata' => ['original_credit_id' => $credit->id],
            ]);

            $credit->update(['amount' => 0]);
            $currentBalance = $newBalance;
        }

        return $totalExpired;
    }

    /**
     * Credits expiring before the given date.
     */
    public function expiringCredits(DateTimeInterface $before): int
    {
        return (int) Credit::query()
            ->where('organization_id', $this->getAttribute('id'))
            ->where('creditable_type', $this->getMorphClass())
            ->where('creditable_id', $this->getKey())
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $before)
            ->where('amount', '>', 0)
            ->sum('amount');
    }

    public function addCredits(
        int $amount,
        CreditTransactionType $type,
        ?string $description = null,
        ?DateTimeInterface $expiresAt = null,
        ?array $metadata = null,
        ?Model $creditable = null
    ): Credit {
        $creditable ??= $this;
        $prev = Credit::query()
            ->where('organization_id', $this->getAttribute('id'))
            ->where('creditable_type', $creditable->getMorphClass())
            ->where('creditable_id', $creditable->getKey())
            ->orderByDesc('id')
            ->first();
        $running = ($prev ? $prev->running_balance : 0) + $amount;

        $credit = Credit::query()->create([
            'organization_id' => $this->getAttribute('id'),
            'creditable_type' => $creditable->getMorphClass(),
            'creditable_id' => $creditable->getKey(),
            'amount' => $amount,
            'running_balance' => $running,
            'type' => $type,
            'description' => $description,
            'expires_at' => $expiresAt,
            'metadata' => $metadata,
        ]);

        event(new CreditsAdded($this, $credit));

        return $credit;
    }

    /**
     * Deduct credits (FIFO, expiration priority). Returns true if deduction was made.
     */
    public function deductCredits(int $amount, string $description = 'Usage', ?array $metadata = null): bool
    {
        $available = $this->availableCredits();
        if ($available < $amount) {
            return false;
        }

        $prev = Credit::query()
            ->where('organization_id', $this->getAttribute('id'))
            ->where('creditable_type', $this->getMorphClass())
            ->where('creditable_id', $this->getKey())
            ->orderByDesc('id')
            ->first();
        $running = ($prev ? $prev->running_balance : 0) - $amount;

        Credit::query()->create([
            'organization_id' => $this->getAttribute('id'),
            'creditable_type' => $this->getMorphClass(),
            'creditable_id' => $this->getKey(),
            'amount' => -$amount,
            'running_balance' => $running,
            'type' => CreditTransactionType::Usage,
            'description' => $description,
            'metadata' => $metadata,
        ]);

        event(new CreditsDeducted($this, $amount));

        return true;
    }

    public function hasCredits(int $amount = 1): bool
    {
        return $this->availableCredits() >= $amount;
    }

    public function grantSubscriptionCredits(int $amount, ?DateTimeInterface $expiresAt = null): Credit
    {
        return $this->addCredits($amount, CreditTransactionType::Subscription, 'Subscription credits', $expiresAt);
    }

    public function grantBonusCredits(int $amount, ?DateTimeInterface $expiresAt = null): Credit
    {
        return $this->addCredits($amount, CreditTransactionType::Bonus, 'Bonus credits', $expiresAt);
    }

    public function purchaseCreditPack(CreditPack $pack): Credit
    {
        $expiresAt = config('billing.credit_expiration_days')
            ? now()->addDays(config('billing.credit_expiration_days'))
            : null;
        $total = $pack->credits + $pack->bonus_credits;

        return $this->addCredits(
            $total,
            CreditTransactionType::Purchase,
            'Purchase: '.$pack->name,
            $expiresAt,
            ['credit_pack_id' => $pack->id]
        );
    }
}
