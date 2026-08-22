<?php

declare(strict_types=1);

namespace LesHttp\Middleware\Route;

use Psr\Container\ContainerInterface;

/**
 * @psalm-immutable
 */
final class DispatchMiddlewareFactory
{
    /**
     * @psalm-pure
     */
    public function __invoke(ContainerInterface $container): DispatchMiddleware
    {
        return new DispatchMiddleware($container);
    }
}
