<?php

declare(strict_types=1);

namespace Modules\Billing\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Billing\Models\Credit;

final readonly class CreditsAdded
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public object $organization,
        public Credit $credit
    ) {}
}
