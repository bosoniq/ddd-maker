<?php

declare(strict_types=1);

namespace App\Service;

class Validator
{
    public function validateProjectStructure(): void
    {
       // TODO: TO BE IMPLEMENTED
    }

    /**
     * @param array{
     *   project_namespace: string,
     *   templates: array<string, array{template: string, additional: array<string>}>
     * } $config
     */
    public function validateConfigStructure(array $config): void
    {
       // TODO: TO BE IMPLEMENTED
    }
}
