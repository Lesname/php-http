<?php
declare(strict_types=1);

namespace LessHttp\Validation;

use LessDocumentor\Route\RouteDocumentor;
use LessValidator\Builder\RouteDocumentValidatorBuilder;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\SimpleCache\CacheInterface;

final class ValidationMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): ValidationMiddleware
    {
        $validatorBuilder = $container->get(RouteDocumentValidatorBuilder::class);
        assert($validatorBuilder instanceof RouteDocumentValidatorBuilder);

        $responseFactory = $container->get(ResponseFactoryInterface::class);
        assert($responseFactory instanceof ResponseFactoryInterface);

        $streamFactory = $container->get(StreamFactoryInterface::class);
        assert($streamFactory instanceof StreamFactoryInterface);

        $routeDocumentor = $container->get(RouteDocumentor::class);
        assert($routeDocumentor instanceof RouteDocumentor);

        $cache = $container->get(CacheInterface::class);
        assert($cache instanceof CacheInterface);

        $config = $container->get('config');
        assert(is_array($config));
        assert(is_array($config['routes']));

        return new ValidationMiddleware(
            $validatorBuilder,
            $responseFactory,
            $streamFactory,
            $routeDocumentor,
            $container,
            $cache,
            $config['routes'],
        );
    }
}
