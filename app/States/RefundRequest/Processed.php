<?php

declare(strict_types=1);

namespace App\States\RefundRequest;

final class Processed extends RefundRequestStatus
{
    public static string $name = 'processed';
}
