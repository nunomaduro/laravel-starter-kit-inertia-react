<?php

declare(strict_types=1);

namespace App\Enums;

enum PropertyType: string
{
    case Resort = 'resort';
    case Hotel = 'hotel';
    case Villa = 'villa';
}
