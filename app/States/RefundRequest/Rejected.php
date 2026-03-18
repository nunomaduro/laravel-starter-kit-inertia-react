<?php

declare(strict_types=1);

namespace App\States\RefundRequest;

final class Rejected extends RefundRequestStatus
{
    public static string $name = 'rejected';
}
