<?php

declare(strict_types=1);

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Declined = 'declined';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
