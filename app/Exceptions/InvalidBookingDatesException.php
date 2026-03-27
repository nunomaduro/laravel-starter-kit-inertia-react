<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class InvalidBookingDatesException extends RuntimeException
{
    public function __construct(string $message = 'The provided booking dates are invalid.')
    {
        parent::__construct($message);
    }
}
