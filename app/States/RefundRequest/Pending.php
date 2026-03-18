<?php

declare(strict_types=1);

namespace App\States\RefundRequest;

final class Pending extends RefundRequestStatus
{
    public static string $name = 'pending';
}
