<?php

declare(strict_types=1);

namespace LesHttp\Middleware\Response;

use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final class CatchExceptionMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): CatchExceptionMiddleware
    {
        $responseFactory = $container->get(ResponseFactoryInterface::class);
        assert($responseFactory instanceof ResponseFactoryInterface);

        $streamFactory = $container->get(StreamFactoryInterface::class);
        assert($streamFactory instanceof StreamFactoryInterface);

        $logger = $container->get(LoggerInterface::class);
        assert($logger instanceof LoggerInterface);

        return new CatchExceptionMiddleware($responseFactory, $streamFactory, $logger);
    }
}
