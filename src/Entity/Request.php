<?php

declare(strict_types=1);

namespace App\Entity;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Symfony\Component\Console\Input\InputInterface;

class Request
{
    private function __construct(
        private readonly string $type,
        private readonly string $context,
        private readonly string $prepend,
        private readonly string $namespace,
        private readonly ?string $parentDirectory,
    ) {
    }

    /**
     * @param array{
     *   project_namespace: string|null,
     *   templates: array<string, array{template: string, additional: array<string>}>
     * } $config
     * @throws AssertionFailedException
     */
    public static function fromInput(InputInterface $input, array $config): self
    {
        $args = $input->getArguments();
        $options = $input->getOptions();

        self::assertValidArguments($args);
        self::assertValidConfig($config);

        return new self(
            $args['type'],
            self::normalizeContext($args['context']),
            $args['prepend'],
            $config['project_namespace'],
            self::normalizeDirectory($options['directory']),
        );
    }


    public function type(): string
    {
        return $this->type;
    }

    public function context(): string
    {
        return $this->context;
    }

    public function prepend(): string
    {
        return $this->prepend;
    }

    public function namespace(): string
    {
        return $this->namespace;
    }

    public function parentDirectory(): string
    {
        return $this->parentDirectory ?? '';
    }

    /**
     * @param array<array<array|bool|float|int|string|null> $arguments
     * @throws AssertionFailedException
     */
    private static function assertValidArguments(array $arguments): void
    {
        Assertion::alnum($arguments['type'] ?? null);
        Assertion::alnum($arguments['context'] ?? null);
        Assertion::alnum($arguments['prepend'] ?? null);
    }

    /**
     * @param array{
     *   project_namespace: string|null,
     *   templates: array<string, array{template: string, additional: array<string>}>
     * } $config
     * @throws AssertionFailedException
     */
    private static function assertValidConfig(array $config): void
    {
        Assertion::notBlank($config['project_namespace'] ?? null);
    }

    private static function normalizeContext(string $context): string
    {
        $context = str_replace('Context', '', $context);
        $context = str_replace('context', '', $context);

        return $context . 'Context';
    }

    private static function normalizeDirectory(?string $directories): ?string
    {
        if (null === $directories) {
            return $directories;
        }

        $directories = explode('/', $directories);

        foreach ($directories as &$directory) {
            $directory = ucfirst($directory);
        }

        return implode('/', $directories);
    }
}
