<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Cors;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final class CorsMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): CorsMiddleware
    {
        $responseFactory = $container->get(ResponseFactoryInterface::class);
        assert($responseFactory instanceof ResponseFactoryInterface);

        $config = $container->get('config');

        assert(is_array($config));
        assert(is_array($config['cors']));
        $cors = $config['cors'];

        assert(is_array($cors['origins']));
        $origins = $cors['origins'];
        /** @var array<string> $origins */

        assert(is_array($cors['methods']));
        $methods = $cors['methods'];
        /** @var array<string> $methods */

        assert(is_array($cors['headers']));
        $headers = $cors['headers'];
        /** @var array<string> $headers */

        assert(is_int($config['cors']['maxAge']));
        $maxAge = $config['cors']['maxAge'];

        return new CorsMiddleware(
            $responseFactory,
            $origins,
            $methods,
            $headers,
            $maxAge,
        );
    }
}
