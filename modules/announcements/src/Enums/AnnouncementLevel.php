<?php

declare(strict_types=1);

namespace Modules\Announcements\Enums;

enum AnnouncementLevel: string
{
    case Info = 'info';
    case Warning = 'warning';
    case Maintenance = 'maintenance';
}
