<?php

declare(strict_types=1);

namespace LesHttp\Middleware\Input;

use Psr\SimpleCache\CacheInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use LesDocumentor\Route\Input\RouteInputDocumentor;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ValidationMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): ValidationMiddleware
    {
        $routeInputDocumentor = $container->get(RouteInputDocumentor::class);
        assert($routeInputDocumentor instanceof RouteInputDocumentor);

        $responseFactory = $container->get(ResponseFactoryInterface::class);
        assert($responseFactory instanceof ResponseFactoryInterface);

        $streamFactory = $container->get(StreamFactoryInterface::class);
        assert($streamFactory instanceof StreamFactoryInterface);

        $translator = $container->get(TranslatorInterface::class);
        assert($translator instanceof TranslatorInterface);

        $cache = $container->get(CacheInterface::class);
        assert($cache instanceof CacheInterface);

        return new ValidationMiddleware(
            $routeInputDocumentor,
            $responseFactory,
            $streamFactory,
            $translator,
            $container,
            $cache,
        );
    }
}
