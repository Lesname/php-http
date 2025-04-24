<?php
declare(strict_types=1);

namespace LesHttpTest\Middleware\Throttle;

use Doctrine\DBAL\Connection;
use LesHttp\Middleware\Throttle\ThrottleMiddleware;
use LesHttp\Middleware\Throttle\ThrottleMiddlewareFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * @covers \LesHttp\Middleware\Throttle\ThrottleMiddlewareFactory
 */
final class ThrottleMiddlewareFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $streamFactory = $this->createMock(StreamFactoryInterface::class);

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
            ->expects(self::exactly(4))
            ->method('get')
            ->willReturnMap(
                [
                    [ResponseFactoryInterface::class, $responseFactory],
                    [StreamFactoryInterface::class, $streamFactory],
                    [Connection::class, $connection],
                    ['config', $config],
                ],
            );

        $factory = new ThrottleMiddlewareFactory();
        $middleware = $factory($container);

        self::assertInstanceOf(ThrottleMiddleware::class, $middleware);
    }
}
