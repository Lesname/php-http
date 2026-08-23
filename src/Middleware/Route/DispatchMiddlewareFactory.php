<?php

declare(strict_types=1);

namespace LesHttp\Middleware\Route;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final class DispatchMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): DispatchMiddleware
    {
        $responseFactory = $container->get(ResponseFactoryInterface::class);
        assert($responseFactory instanceof ResponseFactoryInterface);

        $streamFactory = $container->get('streamFactory');
        assert($streamFactory instanceof StreamFactoryInterface);

        return new DispatchMiddleware(
            $responseFactory,
            $streamFactory,
            $container,
        );
    }
}
