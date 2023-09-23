<?php

declare(strict_types=1);

namespace App\Service;

use Exception;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class FileReader
{
    private const DEFAULT_CONFIG_FILE = '/../../config/config.json';

    private const PROJECT_CONFIG_FILE = '.maker.json';

    public function readTemplateContent(array $config, string $templateKey): string
    {
        $templateName = $config['templates'][$templateKey]['template'];

        if (file_exists($this->getCustomTemplatePath($templateName))) {
            return $this->readCustomTemplate($templateName);
        }

        return $this->readDefaultTemplate($templateName);
    }

    /**
     * @return array{
     *   project_namespace: string,
     *   templates: array<string, array{template: string, additional: array<string>}>
     * }
     *
     * @throws Exception */
    public function loadConfig(): array
    {
        // load defaults
        $default = $this->readFile(__DIR__.self::DEFAULT_CONFIG_FILE);
        $default = json_decode($default, true);

        try {
            // load custom config
            $custom = $this->readFile($this->getCwd().self::PROJECT_CONFIG_FILE);
            $custom = json_decode($custom, true);

            return array_merge($default, $custom);
        } catch (FileNotFoundException) {
            return $default;
        }
    }

    /** @throws Exception */
    private function getCwd(): string
    {
        $cwd = getcwd();

        if (is_bool($cwd)) {
            throw new Exception('Current working directory not found');
        }

        return $cwd . '/';
    }

    /** @throws FileNotFoundException */
    private function readFile(string $path): string
    {
        if (!file_exists($path)) {
            throw new FileNotFoundException();
        }

        $content = file_get_contents($path);

        if (is_bool($content)) {
            throw new FileNotFoundException(); // TODO: FileCannotBeReadException
        }

        return $content;
    }

    private function readCustomTemplate(string $templateName): string
    {
        return $this->readFile($this->getCustomTemplatePath($templateName));
    }

    private function readDefaultTemplate(string $templateName): string
    {
        return $this->readFile(sprintf('%s/../../%s', __DIR__, $templateName));
    }

    private function getCustomTemplatePath(string $templateName): string
    {
        return $this->getCwd().'.templates/'.$templateName;
    }
}
