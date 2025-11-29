<?php

declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Throttle;

use Doctrine\DBAL\Connection;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class ThrottleMiddlewareFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ThrottleMiddleware
    {
        $responseFactory = $container->get(ResponseFactoryInterface::class);
        assert($responseFactory instanceof ResponseFactoryInterface);

        $streamFactory = $container->get(StreamFactoryInterface::class);
        assert($streamFactory instanceof StreamFactoryInterface);

        $connection = $container->get(Connection::class);
        assert($connection instanceof Connection);

        $config = $container->get('config');
        assert(is_array($config));
        assert(is_array($config[ThrottleMiddleware::class]));
        $settings = $config[ThrottleMiddleware::class];

        assert(is_array($settings['limits']));
        $limits = $settings['limits'];
        /** @var array<array{duration: int, points: int}> $limits */

        $usageModifier = $settings['usageModifier'] ?? 25;
        assert(is_int($usageModifier));

        return new ThrottleMiddleware(
            $responseFactory,
            $streamFactory,
            $connection,
            $limits,
            $usageModifier,
        );
    }
}
