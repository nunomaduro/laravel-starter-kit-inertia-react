<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class BookingCannotBeApprovedException extends RuntimeException
{
    public function __construct(string $message = 'The booking cannot be approved.')
    {
        parent::__construct($message);
    }
}
