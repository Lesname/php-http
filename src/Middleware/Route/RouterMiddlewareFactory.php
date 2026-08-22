<?php

declare(strict_types=1);

namespace LesHttp\Middleware\Route;

use LesHttp\Router\Router;
use Psr\Container\ContainerInterface;

final class RouterMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): RouterMiddleware
    {
        $router = $container->get(Router::class);
        assert($router instanceof Router);

        return new RouterMiddleware($router);
    }
}
