<?php

declare(strict_types=1);

namespace Modules\Billing\States\RefundRequest;

final class Processed extends RefundRequestStatus
{
    public static string $name = 'processed';
}
