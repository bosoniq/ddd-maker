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

    public static function fromInput(InputInterface $input, array $config): self
    {
        self::assertValidInput($input);
        self::assertValidConfig($config);

        return new self(
            $input->getArgument('type'),
            self::normalizeContext($input->getArgument('context')),
            $input->getArgument('prepend'),
            $config['project_namespace'],
            $input->getOption('directory'),
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

    /** @throws AssertionFailedException */
    private static function assertValidInput(InputInterface $input): void
    {
        $arguments = $input->getArguments();

        Assertion::notBlank($arguments['type'] ?? null);
        Assertion::notBlank($arguments['context'] ?? null);
        Assertion::notBlank($arguments['prepend'] ?? null);
    }

    /** @throws AssertionFailedException */
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
}
