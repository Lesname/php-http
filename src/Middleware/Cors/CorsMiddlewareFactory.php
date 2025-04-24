<?php
declare(strict_types=1);

namespace LesHttp\Middleware\Cors;

use RuntimeException;
use Psr\Log\LoggerInterface;
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
            $logger = $container->get(LoggerInterface::class);
            assert($logger instanceof LoggerInterface);

            $logger->warning('No defaults cors config found');

            $cors = ['default' => $cors];
        } elseif (!is_array($cors['default'])) {
            throw new RuntimeException('Default settings needs to be an array');
        }

        return new CorsMiddleware(
            $responseFactory,
            // @phpstan-ignore argument.type
            $cors,
            // @phpstan-ignore argument.type
            $cors['default'],
        );
    }
}
