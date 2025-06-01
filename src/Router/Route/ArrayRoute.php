<?php
declare(strict_types=1);

namespace LesHttp\Router\Route;

use LesHttp\Router\Route\Exception\OptionNotSet;

/**
 * @psalm-immutable
 */
final class ArrayRoute implements Route
{
    public function __construct(private readonly array $options)
    {}

    public function getOption(string $key): mixed
    {
        if (!array_key_exists($key, $this->options)) {
            throw new OptionNotSet($key);
        }

        return $this->options[$key];
    }

    public function hasOption(string $key): bool
    {
        return array_key_exists($key, $this->options);
    }
}
