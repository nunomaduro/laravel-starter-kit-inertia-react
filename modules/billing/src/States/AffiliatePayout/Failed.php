<?php

declare(strict_types=1);

namespace Modules\Billing\States\AffiliatePayout;

final class Failed extends PayoutStatus
{
    public static string $name = 'failed';
}
