<?php

declare(strict_types=1);

namespace LesHttp\Router\Route;

use Override;
use LesHttp\Router\Route\Exception\OptionNotSet;

/**
 * @psalm-immutable
 */
final class ArrayRoute implements Route
{
    /**
     * @param array<mixed> $options
     */
    public function __construct(private readonly array $options)
    {}

    #[Override]
    public function getOption(string $key): mixed
    {
        if (!array_key_exists($key, $this->options)) {
            throw new OptionNotSet($key);
        }

        return $this->options[$key];
    }

    #[Override]
    public function hasOption(string $key): bool
    {
        return array_key_exists($key, $this->options);
    }

    /**
     * @return array<mixed>
     */
    #[Override]
    public function toArray(): array
    {
        return $this->options;
    }
}
