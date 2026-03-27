<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class RoomNotAvailableException extends RuntimeException
{
    public function __construct(string $message = 'The selected room is not available for the requested dates.')
    {
        parent::__construct($message);
    }
}
