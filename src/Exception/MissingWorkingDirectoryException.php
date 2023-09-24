<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

class MissingWorkingDirectoryException extends Exception
{
    public static function create(): self
    {
        return new self('Current working directory not found');
    }
}
