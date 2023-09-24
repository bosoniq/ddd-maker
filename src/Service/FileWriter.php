<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\ToGenerate;
use Symfony\Component\Console\Output\OutputInterface;
use App\Exception\FileWriteException;

class FileWriter
{
    /** @param array<ToGenerate> $newClasses */
    public function writeFiles(OutputInterface $output, array $newClasses): void
    {
        foreach ($newClasses as $class) {
            $this->pathPutContents($class->path(), $class->content());
            $output->writeln($class->name().' written successfully');
        }

        $output->writeln('All files written successfully');
    }

    private function pathPutContents(string $filePath, string $contents): void
    {
        if (!is_dir($dir = implode('/', explode('/', $filePath, -1)))) {
            mkdir($dir, 0777, true);
        }

        if (is_bool(file_put_contents($filePath, $contents, 0))) {
            throw FileWriteException::fromPath($filePath);
        }
    }
}
