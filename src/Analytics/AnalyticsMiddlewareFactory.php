<?php
declare(strict_types=1);

namespace LessHttp\Analytics;

use Doctrine\DBAL\DriverManager;
use Psr\Container\ContainerInterface;

final class AnalyticsMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): AnalyticsMiddleware
    {
        $config = $container->get('config');
        assert(is_array($config));

        assert(is_array($config['self']));
        assert(is_string($config['self']['name']));

        assert(is_array($config['databases']));
        assert(is_array($config['databases']['analytics']));

        return new AnalyticsMiddleware(
            DriverManager::getConnection($config['databases']['analytics']),
            $config['self']['name'],
        );
    }
}
