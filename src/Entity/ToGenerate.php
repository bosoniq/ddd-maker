<?php

declare(strict_types=1);

namespace App\Entity;

use PDO;

class ToGenerate
{
    private const EXTENSION = 'php';

    private const TYPE_MAPPINGS = [
        'CommandPage' => 'Infrastructure',
        'Command' => 'Application',
        'CommandHandler' => 'Application',
        'Event' => 'Domain',
        'EventHandler' =>'Application',
        'QueryPage' => 'Infrastructure',
        'Query' => 'Application',
        'QueryHandler' =>'Application',
        'QueryResponse' =>'Application',
        'Finder' => 'Domain',
        'Repository' => 'Domain',
        'DoctrineRepository' => 'Domain',
    ];

    private function __construct(
        private readonly string $name,
        private readonly string $content,
        private readonly ?string $namespace,
        private readonly string $path,
    ) {
    }

    public static function fromRequest(Request $request, string $template, string $type): self
    {
        $type = ucfirst($type);

        $className = self::buildClassName($request, $type);

        $classContainer = self::buildContainer($request->parentDirectory());

        $classContent = self::buildClassContent(
            $request->prepend(),
            $request->context(),
            $request->namespace(),
            $classContainer,
            $template
        );

        $classPath = self::buildPath($request, $type);

        return new self(
            $className,
            $classContent,
            $request->namespace(),
            $classPath,
        );
    }

    public function name(): string
    {
        return $this->name;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function content(): string
    {
        return $this->content;
    }

    public function namespace(): ?string
    {
        return $this->namespace;
    }

    private static function buildClassName(Request $request, string $type): string
    {
        if (false !== strpos($type, 'Page')) {
            $type = 'Page';
        }

        return ucfirst($request->prepend().$type.'.'.self::EXTENSION);
    }

    private static function buildClassContent(
        string $name,
        string $context,
        string $namespace,
        string $container,
        string $template
    ): string {
        $template = str_replace('<NAME>', ucfirst($name), $template);
        $template = str_replace('<CONTEXT>', ucfirst($context), $template);
        $template = str_replace('<NAMESPACE>', ucfirst($namespace), $template);
        $template = str_replace('<CONTAINER>', ucfirst($container), $template);

        return $template;
    }

    private static function buildPath(Request $request, string $type): string
    {
        $className = self::buildClassName($request, $type);
        $dirType = self::modifyTypeForDirectory($type);
        $parentDirectory = $request->parentDirectory();

        if (false !== strpos($type, 'Page')) {
            $parentDirectory = 'Delivery/Rest/'.$parentDirectory;
        }

        if ('' === $parentDirectory) {
            $parentDirectory = null;
        }

        $components = [
            'src',
            $request->context(),
            self::TYPE_MAPPINGS[$type],
            self::preparePathType($dirType),
            $parentDirectory,
            $className,
        ];

        $components = array_filter($components);
        $components = array_values($components);

        return implode('/', $components);
    }

    private static function modifyTypeForDirectory(string $type): string
    {
        $type = str_replace('Response', '', $type);
        return str_replace('Handler', '', $type);
    }

    private static function buildContainer(string $parentDirectory): string
    {
        if ('' === $parentDirectory) {
            return $parentDirectory;
        }

        return '\\'.str_replace('/', '\\', $parentDirectory);
    }

    private static function preparePathType(string $type): ?string
    {
        if (false !== strpos($type, 'Page')) {
            return null;
        }

        return $type;
    }
}
