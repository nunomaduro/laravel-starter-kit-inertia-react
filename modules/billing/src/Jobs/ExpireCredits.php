<?php

declare(strict_types=1);

namespace Modules\Billing\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Modules\Billing\Models\Credit;
use Throwable;

final class ExpireCredits implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $expiredByEntity = Credit::query()
            ->withoutGlobalScopes()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->where('amount', '>', 0)
            ->selectRaw('creditable_type, creditable_id, SUM(amount) as total_expired')
            ->groupBy('creditable_type', 'creditable_id')
            ->get();

        $totalExpired = 0;
        $entitiesAffected = 0;
        $errors = 0;

        foreach ($expiredByEntity as $group) {
            try {
                $creditable = $group->creditable_type::find($group->creditable_id);

                if (! $creditable || ! method_exists($creditable, 'expireCredits')) {
                    Log::warning('Creditable entity does not support credit expiration', [
                        'creditable_type' => $group->creditable_type,
                        'creditable_id' => $group->creditable_id,
                    ]);

                    continue;
                }

                $expiredAmount = $creditable->expireCredits();
                $totalExpired += $expiredAmount;
                $entitiesAffected++;

                Log::info('Credits expired for entity', [
                    'creditable_type' => $group->creditable_type,
                    'creditable_id' => $group->creditable_id,
                    'amount' => $expiredAmount,
                ]);
            } catch (Throwable $e) {
                $errors++;
                Log::error('Failed to expire credits for entity', [
                    'creditable_type' => $group->creditable_type,
                    'creditable_id' => $group->creditable_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Credit expiration job completed', [
            'total_expired' => $totalExpired,
            'entities_affected' => $entitiesAffected,
            'errors' => $errors,
        ]);
    }
}
