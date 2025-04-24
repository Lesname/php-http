<?php
declare(strict_types=1);

namespace LesHttp\Middleware\Analytics;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class AnalyticsMiddlewareFactory
{
    /**
     * @psalm-suppress MixedArgumentTypeCoercion
     *
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AnalyticsMiddleware
    {
        $config = $container->get('config');
        assert(is_array($config));

        assert(is_array($config['self']));
        assert(is_string($config['self']['name']));

        assert(is_array($config['databases']));
        assert(is_array($config['databases']['analytics']));

        return new AnalyticsMiddleware(
            // @phpstan-ignore argument.type
            DriverManager::getConnection($config['databases']['analytics']),
            $config['self']['name'],
        );
    }
}
