<?php
declare(strict_types=1);

namespace LessHttpTest\Middleware\Authorization;

use LessHttp\Middleware\Authorization\AuthorizationMiddleware;
use LessHttp\Middleware\Authorization\AuthorizationMiddlewareFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

/**
 * @covers \LessHttp\Middleware\Authorization\AuthorizationMiddlewareFactory
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
