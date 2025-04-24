<?php
declare(strict_types=1);

namespace LesHttp\Middleware\Validation;

use Psr\Log\LoggerInterface;
use LesValidator\Builder\ValidatorBuilder;
use LesDocumentor\Route\Input\RouteInputDocumentor;
use Symfony\Contracts\Translation\TranslatorInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\SimpleCache\CacheInterface;

final class ValidationMiddlewareFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
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

        $logger = $container->get(LoggerInterface::class);
        assert($logger instanceof LoggerInterface);

        $cache = $container->get(CacheInterface::class);
        assert($cache instanceof CacheInterface);

        $config = $container->get('config');
        assert(is_array($config));
        assert(is_array($config['routes']));
        $routes = $config['routes'];
        /** @var array<string, array<mixed>> $routes */

        return new ValidationMiddleware(
            $routeInputDocumentor,
            $responseFactory,
            $streamFactory,
            $translator,
            $container,
            $logger,
            $cache,
            $routes,
        );
    }
}
