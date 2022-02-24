<?php
declare(strict_types=1);

namespace LessHttpTest\Middleware\Throttle;

use Doctrine\DBAL\Connection;
use LessHttp\Middleware\Throttle\ThrottleMiddleware;
use LessHttp\Middleware\Throttle\ThrottleMiddlewareFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

/**
 * @covers \LessHttp\Middleware\Throttle\ThrottleMiddlewareFactory
 */
final class ThrottleMiddlewareFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);

        $connection = $this->createMock(Connection::class);

        $config = [
            ThrottleMiddleware::class => [
                'limits' => [
                    [
                        'duration' => 12,
                        'points' => 3,
                    ],
                ],
            ],
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects(self::exactly(3))
            ->method('get')
            ->willReturnMap(
                [
                    [ResponseFactoryInterface::class, $responseFactory],
                    [Connection::class, $connection],
                    ['config', $config],
                ],
            );

        $factory = new ThrottleMiddlewareFactory();
        $middleware = $factory($container);

        self::assertInstanceOf(ThrottleMiddleware::class, $middleware);
    }
}
