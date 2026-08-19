<?php

declare(strict_types=1);

namespace LesHttpTest\Middleware\AccessControl\Authorization;

use LesHttp\Router\Route\Route;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use LesHttp\Middleware\AccessControl\Authorization\AuthorizationMiddleware;
use LesHttp\Middleware\AccessControl\Authorization\Constraint\AuthorizationConstraint;
use LesHttp\Middleware\AccessControl\Authorization\Constraint\Chain\AuthorizationConstraintChain;

final class AuthorizationMiddlewareTest extends TestCase
{
    public function testAllowed(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $constraint = $this->mockAuthorizationConstraint($request, true);

        $route = $this->createMock(Route::class);
        $route
            ->method('getOption')
            ->with('authorizations')
            ->willReturn([$constraint::class]);

        $request
            ->method('getAttribute')
            ->with('route')
            ->willReturn($route);

        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('get')
            ->with($constraint::class)
            ->willReturn($constraint);

        $middleware = new AuthorizationMiddleware(
            $this->createMock(ResponseFactoryInterface::class),
            $this->createMock(StreamFactoryInterface::class),
            $container,
        );

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testNestedAllowed(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $route = $this->createMock(Route::class);
        $route
            ->method('getOption')
            ->with('authorizations')
            ->willReturn(
                [
                    AuthorizationConstraintChain::and(
                        [
                            AuthorizationConstraintChain::or(
                                [
                                    $this->mockAuthorizationConstraint($request, false),
                                    $this->mockAuthorizationConstraint($request, true),
                                ],
                            ),
                            $this->mockAuthorizationConstraint($request, true),
                        ],
                    ),
                ],
            );

        $request
            ->method('getAttribute')
            ->with('route')
            ->willReturn($route);

        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $middleware = new AuthorizationMiddleware(
            $this->createMock(ResponseFactoryInterface::class),
            $this->createMock(StreamFactoryInterface::class),
            $this->createMock(ContainerInterface::class),
        );

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testNotAllowed(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $stream = $this->createMock(StreamInterface::class);

        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory
            ->expects(self::once())
            ->method('createStream')
            ->willReturn($stream);

        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects(self::once())
            ->method('withBody')
            ->with($stream)
            ->willReturn($response);
        $response
            ->expects(self::once())
            ->method('withAddedHeader')
            ->with('content-type', 'application/json')
            ->willReturn($response);

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

        $route = $this->createMock(Route::class);
        $route
            ->method('getOption')
            ->with('authorizations')
            ->willReturn([$constraint::class]);

        $request
            ->method('getAttribute')
            ->with('route')
            ->willReturn($route);

        $middleware = new AuthorizationMiddleware(
            $responseFactory,
            $streamFactory,
            $container,
        );

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testNestedNotAllowed(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $stream = $this->createMock(StreamInterface::class);

        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory
            ->expects(self::once())
            ->method('createStream')
            ->willReturn($stream);

        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects(self::once())
            ->method('withBody')
            ->with($stream)
            ->willReturn($response);
        $response
            ->expects(self::once())
            ->method('withAddedHeader')
            ->with('content-type', 'application/json')
            ->willReturn($response);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects(self::never())
            ->method('handle');

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $responseFactory
            ->expects(self::once())
            ->method('createResponse')
            ->with(403)
            ->willReturn($response);

        $route = $this->createMock(Route::class);
        $route
            ->method('getOption')
            ->with('authorizations')
            ->willReturn(
                [
                    AuthorizationConstraintChain::and(
                        [
                            $this->mockAuthorizationConstraint($request, true),
                            AuthorizationConstraintChain::or(
                                [
                                    $this->mockAuthorizationConstraint($request, false),
                                    $this->mockAuthorizationConstraint($request, false),
                                ],
                            ),
                        ],
                    )
                ]
            );

        $request
            ->method('getAttribute')
            ->with('route')
            ->willReturn($route);

        $middleware = new AuthorizationMiddleware(
            $responseFactory,
            $streamFactory,
            $this->createMock(ContainerInterface::class),
        );

        self::assertSame($response, $middleware->process($request, $handler));
    }

    private function mockAuthorizationConstraint(ServerRequestInterface $request, bool $allowed): AuthorizationConstraint
    {
        $constraint = $this->createMock(AuthorizationConstraint::class);
        $constraint
            ->expects(self::once())
            ->method('isAllowed')
            ->with($request)
            ->willReturn($allowed);

        return $constraint;
    }
}
