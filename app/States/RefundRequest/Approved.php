<?php

declare(strict_types=1);

namespace App\States\RefundRequest;

final class Approved extends RefundRequestStatus
{
    public static string $name = 'approved';
}
