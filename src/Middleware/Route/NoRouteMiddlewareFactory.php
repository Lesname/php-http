<?php

declare(strict_types=1);

namespace LesHttp\Middleware\Route;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final class NoRouteMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): NoRouteMiddleware
    {
        $responseFactory = $container->get(ResponseFactoryInterface::class);
        assert($responseFactory instanceof ResponseFactoryInterface);

        $streamFactory = $container->get(StreamFactoryInterface::class);
        assert($streamFactory instanceof StreamFactoryInterface);

        return new NoRouteMiddleware($responseFactory, $streamFactory);
    }
}
