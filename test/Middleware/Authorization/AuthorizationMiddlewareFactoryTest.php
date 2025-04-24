<?php
declare(strict_types=1);

namespace LesHttpTest\Middleware\Authorization;

use LesHttp\Middleware\Authorization\AuthorizationMiddleware;
use LesHttp\Middleware\Authorization\AuthorizationMiddlewareFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * @covers \LesHttp\Middleware\Authorization\AuthorizationMiddlewareFactory
 */
final class AuthorizationMiddlewareFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $streamFactory = $this->createMock(StreamFactoryInterface::class);

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('get')
            ->willReturnMap(
                [
                    [
                        'config',
                        [
                            'routes' => [
                                'a' => [
                                    'authorizations' => ['b'],
                                ],
                                'c' => [],
                            ],
                        ],
                    ],
                    [
                        ResponseFactoryInterface::class,
                        $responseFactory,
                    ],
                    [
                        StreamFactoryInterface::class,
                        $streamFactory,
                    ],
                ],
            );

        $factory = new AuthorizationMiddlewareFactory();
        $result = $factory($container);

        self::assertInstanceOf(AuthorizationMiddleware::class, $result);
        self::assertSame(
            ['a' => ['b']],
            $result->getAuthorizations(),
        );
    }
}
