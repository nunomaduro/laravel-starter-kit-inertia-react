<?php

declare(strict_types=1);

namespace App\Enums;

enum AnnouncementLevel: string
{
    case Info = 'info';
    case Warning = 'warning';
    case Maintenance = 'maintenance';
}
