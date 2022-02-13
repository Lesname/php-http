<?php
declare(strict_types=1);

namespace LessHttp\Prerequisite;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class PrerequisiteMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): PrerequisiteMiddleware
    {
        $responseFactory = $container->get(ResponseFactoryInterface::class);
        assert($responseFactory instanceof ResponseFactoryInterface);

        $streamFactory = $container->get(StreamFactoryInterface::class);
        assert($streamFactory instanceof StreamFactoryInterface);

        $config = $container->get('config');
        assert(is_array($config));

        assert(is_array($config['routes']));
        $routes = $config['routes'];
        /** @var array<string, array<mixed>> $routes */

        return new PrerequisiteMiddleware(
            $responseFactory,
            $streamFactory,
            $container,
            $routes,
        );
    }
}
