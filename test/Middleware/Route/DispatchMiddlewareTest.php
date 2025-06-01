<?php
declare(strict_types=1);

namespace LesHttpTest\Middleware\Route;

use LesHttp\Router\Route\Route;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use LesHttp\Middleware\Route\DispatchMiddleware;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DispatchMiddleware::class)]
class DispatchMiddlewareTest extends TestCase
{
    public function testDispatch(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);

        $dispatcher = $this->createMock(RequestHandlerInterface::class);

        $dispatcher
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $route = $this->createMock(Route::class);
        $route
            ->method('getOption')
            ->with('middleware')
            ->willReturn($dispatcher::class);

        $request
            ->method('getAttribute')
            ->with('route')
            ->willReturn($route);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects(self::once())
            ->method('get')
            ->with($dispatcher::class)
            ->willReturn($dispatcher);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::never())->method('handle');

        $middleware = new DispatchMiddleware($container);
        self::assertSame($response, $middleware->process($request, $handler));
    }
}
