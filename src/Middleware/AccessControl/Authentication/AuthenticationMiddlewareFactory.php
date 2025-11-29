<?php

declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Authentication;

use Psr\Container\ContainerInterface;
use LesHttp\Middleware\AccessControl\Authentication\Adapter\AuthenticationAdapterHelper;

final class AuthenticationMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): AuthenticationMiddleware
    {
        $config = $container->get('config');

        assert(is_array($config));
        assert(is_array($config[AuthenticationMiddleware::class]));
        assert(is_array($config[AuthenticationMiddleware::class]['adapters']));

        $adapters = [];

        foreach ($config[AuthenticationMiddleware::class]['adapters'] as $adapter) {
            assert(is_array($adapter));

            $adapters[] = AuthenticationAdapterHelper::fromConfig($adapter);
        }

        return new AuthenticationMiddleware($adapters);
    }
}
