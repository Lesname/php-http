<?php
declare(strict_types=1);

namespace LessHttpTest\Authentication;

use LessHttp\Authentication\Adapter\AuthenticationAdapter;
use LessHttp\Authentication\AuthenticationMiddleware;
use LessValueObject\Composite\ForeignReference;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @covers \LessHttp\Authentication\AuthenticationMiddleware
 */
final class AuthenticationMiddlewareTest extends TestCase
{
    public function testNoResolve(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $adapter = $this->createMock(AuthenticationAdapter::class);
        $adapter
            ->expects(self::once())
            ->method('resolve')
            ->with($request)
            ->willReturn(null);

        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $middleware = new AuthenticationMiddleware($adapter);

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testResolve(): void
    {
        $reference = ForeignReference::fromString('abc/9cd78005-5c15-40a3-8dd5-6836cee2ee81');

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects(self::once())
            ->method('withAttribute')
            ->with('identity', $reference)
            ->willReturn($request);

        $adapter = $this->createMock(AuthenticationAdapter::class);
        $adapter
            ->expects(self::once())
            ->method('resolve')
            ->with($request)
            ->willReturn($reference);

        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $middleware = new AuthenticationMiddleware($adapter);

        self::assertSame($response, $middleware->process($request, $handler));
    }
}
