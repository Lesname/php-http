<?php

declare(strict_types=1);

namespace LesHttp\Middleware\Input\Decode;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final class JsonMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): JsonMiddleware
    {
        $responseFactory = $container->get(ResponseFactoryInterface::class);
        assert($responseFactory instanceof ResponseFactoryInterface);

        $streamFactory = $container->get(StreamFactoryInterface::class);
        assert($streamFactory instanceof StreamFactoryInterface);

        return new JsonMiddleware($responseFactory, $streamFactory);
    }
}
