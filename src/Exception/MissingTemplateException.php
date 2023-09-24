<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

class InvalidRequestTypeException extends Exception
{
    /**
     * @param mixed string
     *
     * @return void
     */
    public static function fromType(string $type): self
    {
        return new self('No template available for type: ' .$type);
    }
}
