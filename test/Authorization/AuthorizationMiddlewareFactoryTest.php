<?php
declare(strict_types=1);

namespace LessHttpTest\Authorization;

use LessHttp\Authorization\AuthorizationMiddleware;
use LessHttp\Authorization\AuthorizationMiddlewareFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

/**
 * @covers \LessHttp\Authorization\AuthorizationMiddlewareFactory
 */
final class AuthorizationMiddlewareFactoryTest extends TestCase
{
    public function testCreate(): void
    {
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

                            ],
                        ],
                    ],
                    [
                        ResponseFactoryInterface::class,
                        $responseFactory,
                    ],
                ],
            );

        $factory = new AuthorizationMiddlewareFactory();
        $result = $factory($container);

        self::assertInstanceOf(AuthorizationMiddleware::class, $result);
    }
}
