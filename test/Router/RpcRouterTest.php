<?php
declare(strict_types=1);

namespace LesHttpTest\Router;

use LesHttp\Router\RpcRouter;
use PHPUnit\Framework\TestCase;
use LesHttp\Router\Route\Route;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\RequestInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RpcRouter::class)]
class RpcRouterTest extends TestCase
{
    public function testMatch(): void
    {
        $router = new RpcRouter(
            [
                'post:/foo' => ['fiz' => 'biz'],
            ],
        );

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/foo');

        $request = $this->createMock(RequestInterface::class);
        $request->method('getMethod')->willReturn('POST');
        $request->method('getUri')->willReturn($uri);

        $route = $router->match($request);

        self::assertInstanceOf(Route::class, $route);
        self::assertSame('biz', $route->getOption('fiz'));
    }

    public function testMatchFallback(): void
    {
        $router = new RpcRouter(
            [
                'query:/foo' => ['fiz' => 'biz'],
            ],
        );

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/foo');

        $request = $this->createMock(RequestInterface::class);
        $request->method('getMethod')->willReturn('POST');
        $request->method('getUri')->willReturn($uri);

        $route = $router->match($request);

        self::assertInstanceOf(Route::class, $route);
        self::assertSame('biz', $route->getOption('fiz'));
    }

    public function testNoMatch(): void
    {
        $router = new RpcRouter(
            [
                'query:/bar' => ['fiz' => 'biz'],
            ],
        );

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/foo');

        $request = $this->createMock(RequestInterface::class);
        $request->method('getMethod')->willReturn('POST');
        $request->method('getUri')->willReturn($uri);

        $route = $router->match($request);

        self::assertNull($route);
    }
}
