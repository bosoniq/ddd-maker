<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

class FileCannotBeReadException extends Exception
{
    public static function fromPath(string $path): self
    {
        return new self("File cannot be read at $path");
    }
}
