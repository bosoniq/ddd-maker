<?php

declare(strict_types=1);

namespace App\Service;

class FileWriter
{
    /** @param array<ToGenerate> $files */
    public function writeFiles(array $newClasses): void
    {
        foreach ($newClasses as $class) {
            $this->pathPutContents($class->path(), $class->content());
        }

        echo "Files written successfully";
    }

    private function pathPutContents(string $filePath, string $contents): void
    {
        if (!is_dir($dir = implode('/', explode('/', $filePath, -1)))) {
            mkdir($dir, 0777, true);
        }

        // TODO: Handle errors here
        file_put_contents($filePath, $contents, 0);
    }
}
