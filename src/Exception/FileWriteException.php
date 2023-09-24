<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

class FileWriteException extends Exception
{
    public static function fromPath(string $path): self
    {
        return new self("Cannot write to $path");
    }
}
