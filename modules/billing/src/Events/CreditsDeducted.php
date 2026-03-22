<?php

declare(strict_types=1);

namespace Modules\Billing\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final readonly class CreditsDeducted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public object $organization,
        public int $amount
    ) {}
}
