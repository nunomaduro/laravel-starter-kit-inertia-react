<?php

declare(strict_types=1);

namespace Modules\Billing\States\RefundRequest;

final class Rejected extends RefundRequestStatus
{
    public static string $name = 'rejected';
}
