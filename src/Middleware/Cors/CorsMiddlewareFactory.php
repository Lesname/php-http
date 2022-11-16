<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Cors;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final class CorsMiddlewareFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @psalm-suppress MixedArgumentTypeCoercion array not checked
     */
    public function __invoke(ContainerInterface $container): CorsMiddleware
    {
        $responseFactory = $container->get(ResponseFactoryInterface::class);
        assert($responseFactory instanceof ResponseFactoryInterface);

        $config = $container->get('config');

        assert(is_array($config));
        assert(is_array($config['cors']));
        $cors = $config['cors'];

        if (!isset($cors['default'])) {
            $cors = ['default' => $cors];
        }

        return new CorsMiddleware(
            $responseFactory,
            $cors,
        );
    }
}
