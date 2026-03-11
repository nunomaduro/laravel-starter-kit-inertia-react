<?php

declare(strict_types=1);

namespace App\Enums;

enum AnnouncementScope: string
{
    case Global = 'global';
    case Organization = 'organization';
}
