<?php

declare(strict_types=1);

namespace Modules\Billing\States\RefundRequest;

final class Approved extends RefundRequestStatus
{
    public static string $name = 'approved';
}
