<?php

declare(strict_types=1);

namespace App\Models\Billing;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class FailedPaymentAttempt extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'organization_id',
        'gateway',
        'gateway_subscription_id',
        'attempt_number',
        'dunning_emails_sent',
        'failed_at',
        'last_dunning_sent_at',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    protected function casts(): array
    {
        return [
            'attempt_number' => 'integer',
            'failed_at' => 'datetime',
            'last_dunning_sent_at' => 'datetime',
        ];
    }
}
