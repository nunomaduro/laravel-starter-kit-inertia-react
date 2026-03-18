<?php

declare(strict_types=1);

namespace Modules\Announcements\Enums;

enum AnnouncementScope: string
{
    case Global = 'global';
    case Organization = 'organization';
}
