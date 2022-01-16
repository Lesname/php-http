<?php
declare(strict_types=1);

namespace LessHttpTest\Authorization;

use LessHttp\Authorization\AuthorizationMiddleware;
use LessHttp\Authorization\Constraint\AuthorizationConstraint;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @covers \LessHttp\Authorization\AuthorizationMiddleware
 */
final class AuthorizationMiddlewareTest extends TestCase
{
    public function testAllowed(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri
            ->method('getPath')
            ->willReturn('/foo');

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getUri')
            ->willReturn($uri);

        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $constraint = $this->createMock(AuthorizationConstraint::class);
        $constraint
            ->expects(self::once())
            ->method('isAllowed')
            ->with($request)
            ->willReturn(true);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('get')
            ->with($constraint::class)
            ->willReturn($constraint);

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);

        $middleware = new AuthorizationMiddleware(
            $responseFactory,
            $container,
            [
                '/foo' => [
                    'authorizations' => [
                        $constraint::class,
                    ],
                ],
            ],
        );

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testNotAllowed(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri
            ->method('getPath')
            ->willReturn('/foo');

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getUri')
            ->willReturn($uri);

        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects(self::never())
            ->method('handle');

        $constraint = $this->createMock(AuthorizationConstraint::class);
        $constraint
            ->expects(self::once())
            ->method('isAllowed')
            ->with($request)
            ->willReturn(false);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('get')
            ->with($constraint::class)
            ->willReturn($constraint);

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $responseFactory
            ->expects(self::once())
            ->method('createResponse')
            ->with(403)
            ->willReturn($response);

        $middleware = new AuthorizationMiddleware(
            $responseFactory,
            $container,
            [
                '/foo' => [
                    'authorizations' => [
                        $constraint::class,
                    ],
                ],
            ],
        );

        self::assertSame($response, $middleware->process($request, $handler));
    }
}
