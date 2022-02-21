<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Throttle;

use Doctrine\DBAL\Connection;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final class ThrottleMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): ThrottleMiddleware
    {
        $responseFactory = $container->get(ResponseFactoryInterface::class);
        assert($responseFactory instanceof ResponseFactoryInterface);

        $connection = $container->get(Connection::class);
        assert($connection instanceof Connection);

        $config = $container->get('config');
        assert(is_array($config));
        assert(is_array($config[ThrottleMiddleware::class]));
        $settings = $config[ThrottleMiddleware::class];

        assert(is_array($settings['limits']));
        $limits = $settings['limits'];
        /** @var array<array{duration: int, points: int}> $limits */

        return new ThrottleMiddleware($responseFactory, $connection, $limits);
    }
}
