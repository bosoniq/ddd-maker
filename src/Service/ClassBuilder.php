<?php

declare(strict_types=1);

namespace App\Service;

use Exception;
use App\Entity\Request;
use App\Entity\ToGenerate;
use App\Exception\MissingTemplateException;
use App\Service\FileReader;

class ClassBuilder
{
    public function __construct(private readonly FileReader $fileReader)
    {
    }

    /**
     * @param array{
     *   project_namespace: string,
     *   templates: array<string, array{template: string, additional: array<string>}>
     * } $config
     * @return array<ToGenerate>
     */
    public function prepareNewClassObjects(Request $request, array $config): array
    {
        $toGenerate = [];
        $templateKeys = $this->getTemplateKeys($request, $config);

        foreach ($templateKeys as $templateKey) {
            $this->ensureConfigKeyExists($config, $templateKey);

            $content = $this->fileReader->readTemplateContent($config, $templateKey);

            $toGenerate[] = ToGenerate::fromRequest($request, $content, $templateKey);
        }

        return $toGenerate;
    }

    /**
     * @param array{
     *   project_namespace: string,
     *   templates: array<string, array{template: string, additional: array<string>}>
     * } $config
     * @throws MissingTemplateException
     */
    private function ensureConfigKeyExists(array $config, string $type): void
    {
        if (!array_key_exists($type, $config['templates'])) {
            throw MissingTemplateException::fromType($type);
        }
    }

    /**
     * @param array{
     *   project_namespace: string,
     *   templates: array<string, array{template: string, additional: array<string>}>
     * } $config
     * @return array<string>
     */
    private function getTemplateKeys(Request $request, array $config): array
    {
        $this->ensureConfigKeyExists($config, $request->type());

        $additional = $config['templates'][$request->type()]['additional'] ?? [];

        return [$request->type(), ...$additional];
    }
}
