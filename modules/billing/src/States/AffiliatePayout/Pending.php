<?php

declare(strict_types=1);

namespace Modules\Billing\States\AffiliatePayout;

final class Pending extends PayoutStatus
{
    public static string $name = 'pending';
}
