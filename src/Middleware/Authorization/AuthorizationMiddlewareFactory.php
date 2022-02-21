<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Authorization;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final class AuthorizationMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): AuthorizationMiddleware
    {
        $responseFactory = $container->get(ResponseFactoryInterface::class);
        assert($responseFactory instanceof ResponseFactoryInterface);

        $config = $container->get('config');
        assert(is_array($config));

        assert(is_array($config['routes']));
        $routes = $config['routes'];
        /** @var array<string, array<mixed>> $routes */

        return new AuthorizationMiddleware($responseFactory, $container, $routes);
    }
}
