<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;

class FileNotFoundException extends Exception
{
    public static function fromPath(string $path): self
    {
        return new self("File cannot be found at $path");
    }
}
