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
     */
    public function getOption(string $key): mixed;

    public function hasOption(string $key): bool;
}
