<?php

declare(strict_types=1);

namespace LesHttp\Router\Route;

use LesHttp\Router\Route\Exception\OptionNotSet;

/**
 * @psalm-immutable
 */
interface Route
{
    /**
     * @throws OptionNotSet
     *
     * @psalm-mutation-free
     */
    public function getOption(string $key): mixed;

    /**
     * @psalm-mutation-free
     */
    public function hasOption(string $key): bool;

    /**
     * @return array<mixed>
     *
     * @psalm-mutation-free
     */
    public function toArray(): array;
}
