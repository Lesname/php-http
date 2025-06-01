<?php
declare(strict_types=1);

namespace LesHttpTest\Middleware\Route;

use LesHttp\Router\Route\Route;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use LesHttp\Middleware\Route\NoRouteMiddleware;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NoRouteMiddleware::class)]
class NoRouteMiddlewareTest extends TestCase
{
    public function testWithRoute(): void
    {
        $route = $this->createMock(Route::class);

        $request = $this->createMock(ServerRequestInterface::class);
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

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $responseFactory->expects(self::never())->method('createResponse');

        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory->expects(self::never())->method('createStream');

        $middleware = new NoRouteMiddleware($responseFactory, $streamFactory);

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testWithoutRoute(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getAttribute')
            ->with('route')
            ->willReturn(null);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::never())->method('handle');

        $stream = $this->createMock(StreamInterface::class);

        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory
            ->expects(self::once())
            ->method('createStream')
            ->willReturn($stream);

        $response = $this->createMock(ResponseInterface::class);

        $response
            ->expects(self::once())
            ->method('withHeader')
            ->with('content-type', 'application/json')
            ->willReturn($response);

        $response
            ->expects(self::once())
            ->method('withBody')
            ->with($stream)
            ->willReturn($response);

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $responseFactory
            ->expects(self::once())
            ->method('createResponse')
            ->with(404)
            ->willReturn($response);

        $middleware = new NoRouteMiddleware($responseFactory, $streamFactory);

        self::assertSame($response, $middleware->process($request, $handler));
    }
}
