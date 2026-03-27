<?php

declare(strict_types=1);

namespace App\Enums;

enum CancelledBy: string
{
    case Guest = 'guest';
    case Host = 'host';
}
