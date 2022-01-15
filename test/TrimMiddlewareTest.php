<?php
declare(strict_types=1);

namespace LessHttpTest;

use LessHttp\TrimMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @covers \LessHttp\TrimMiddleware
 */
final class TrimMiddlewareTest extends TestCase
{
    public function testProcessArray(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects(self::once())
            ->method('getParsedBody')
            ->willReturn(
                [
                    'foo' => [
                        'bar' => ' biz ',
                    ],
                ],
            );

        $request
            ->expects(self::once())
            ->method('withParsedBody')
            ->with(
                [
                    'foo' => [
                        'bar' => 'biz',
                    ],
                ],
            )
            ->willReturn($request);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $middleware = new TrimMiddleware();

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testProcessObject(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects(self::once())
            ->method('getParsedBody')
            ->willReturn(
                (object)[
                    'foo' => (object)[
                        'bar' => 1,
                    ],
                ],
            );

        $request
            ->expects(self::once())
            ->method('withParsedBody')
            ->with(
                (object)[
                    'foo' => (object)[
                        'bar' => 1,
                    ],
                ],
            )
            ->willReturn($request);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $middleware = new TrimMiddleware();

        self::assertSame($response, $middleware->process($request, $handler));
    }
}
