<?php

declare(strict_types=1);

namespace LesHttpTest\Middleware\Route;

use LesHttp\Router\Route\Route;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use LesHttp\Middleware\Route\DispatchMiddleware;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use LesHttp\Middleware\Route\Handler\RouteHandler;
use LesHttp\Middleware\Route\Handler\Response\HandleResponse;

#[CoversClass(DispatchMiddleware::class)]
class DispatchMiddlewareTest extends TestCase
{
    public function testDispatchRequestHandler(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);

        $requestHandler = $this->createMock(RequestHandlerInterface::class);

        $requestHandler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $route = $this->createMock(Route::class);
        $route
            ->expects(self::exactly(2))
            ->method('hasOption')
            ->willReturnMap(
                [
                    ['handler', false],
                    ['middleware', true],
                ],
            );
        $route
            ->method('getOption')
            ->with('middleware')
            ->willReturn($requestHandler::class);

        $request
            ->method('getAttribute')
            ->with('route')
            ->willReturn($route);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects(self::once())
            ->method('get')
            ->with($requestHandler::class)
            ->willReturn($requestHandler);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::never())->method('handle');

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);

        $streamFactory = $this->createMock(StreamFactoryInterface::class);

        $middleware = new DispatchMiddleware($responseFactory, $streamFactory, $container);
        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testDispatchRouteHandler(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $route = $this->createMock(Route::class);

        $stream = $this->createMock(StreamInterface::class);

        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects(self::once())
            ->method('withBody')
            ->with($stream)
            ->willReturn($response);

        $response
            ->expects(self::once())
            ->method('withHeader')
            ->with('content-type', 'application/json')
            ->willReturn($response);

        $routeHandler = $this->createMock(RouteHandler::class);

        $handleResponse = new class implements HandleResponse {
            public int $code = 245;
            public mixed $body = [];
            public array $headers = [];
        };

        $routeHandler
            ->expects(self::once())
            ->method('handle')
            ->with($request, $route)
            ->willReturn($handleResponse);

        $route
            ->expects(self::once(2))
            ->method('hasOption')
            ->willReturnMap([['handler', true]]);
        $route
            ->method('getOption')
            ->with('handler')
            ->willReturn($routeHandler::class);

        $request
            ->method('getAttribute')
            ->with('route')
            ->willReturn($route);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects(self::once())
            ->method('get')
            ->with($routeHandler::class)
            ->willReturn($routeHandler);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::never())->method('handle');

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $responseFactory
            ->expects(self::once())
            ->method('createResponse')
            ->with(245, '')
            ->willReturn($response);

        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory
            ->expects(self::once())
            ->method('createStream')
            ->with('[]')
            ->willReturn($stream);

        $middleware = new DispatchMiddleware($responseFactory, $streamFactory, $container);
        self::assertSame($response, $middleware->process($request, $handler));
    }
}
