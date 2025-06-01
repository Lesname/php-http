<?php
declare(strict_types=1);

namespace LesHttpTest\Middleware\Route;

use LesHttp\Router\Router;
use LesHttp\Router\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use LesHttp\Middleware\Route\RouterMiddleware;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RouterMiddleware::class)]
class RouterMiddlewareTest extends TestCase
{
    public function testWithMatch(): void
    {
        $route = $this->createMock(Route::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects(self::once())
            ->method('withAttribute')
            ->with('route', $route)
            ->willReturn($request);

        $router = $this->createMock(Router::class);
        $router
            ->expects(self::once())
            ->method('match')
            ->with($request)
            ->willReturn($route);

        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $middleware = new RouterMiddleware($router);

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testWithoutMatch(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::never())->method('withAttribute');

        $router = $this->createMock(Router::class);
        $router
            ->expects(self::once())
            ->method('match')
            ->with($request)
            ->willReturn(null);

        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $middleware = new RouterMiddleware($router);

        self::assertSame($response, $middleware->process($request, $handler));
    }
}
