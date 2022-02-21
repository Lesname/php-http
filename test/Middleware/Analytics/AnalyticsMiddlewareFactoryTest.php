<?php
declare(strict_types=1);

namespace LessHttpTest\Middleware\Analytics;

use LessHttp\Middleware\Analytics\AnalyticsMiddleware;
use LessHttp\Middleware\Analytics\AnalyticsMiddlewareFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \LessHttp\Middleware\Analytics\AnalyticsMiddlewareFactory
 */
final class AnalyticsMiddlewareFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $config = [
            'databases' => [
                'analytics' => [
                    'url' => 'mysql://user:password@localhost/db_name?charset=UTF8',
                ],
            ],
            'self' => [
                'name' => 'foo',
            ],
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $factory = new AnalyticsMiddlewareFactory();
        $middleware = $factory($container);

        self::assertInstanceOf(AnalyticsMiddleware::class, $middleware);
    }
}
