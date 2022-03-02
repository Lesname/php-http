<?php
declare(strict_types=1);

namespace LessHttpTest\Middleware\Prerequisite;

use LessHttp\Middleware\Prerequisite\Constraint\PrerequisiteConstraint;
use LessHttp\Middleware\Prerequisite\PrerequisiteMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @covers \LessHttp\Middleware\Prerequisite\PrerequisiteMiddleware
 */
final class PrerequisiteMiddlewareTest extends TestCase
{
    public function testSatisfied(): void
    {
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);

        $streamFactory = $this->createMock(StreamFactoryInterface::class);

        $response = $this->createMock(ResponseInterface::class);

        $uri = $this->createMock(UriInterface::class);
        $uri
            ->method('getPath')
            ->willReturn('/foo');

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getUri')
            ->willReturn($uri);

        $request
            ->method('getMethod')
            ->willReturn('POST');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $prerequisite = $this->createMock(PrerequisiteConstraint::class);
        $prerequisite
            ->expects(self::once())
            ->method('isSatisfied')
            ->with($request)
            ->willReturn(true);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects(self::once())
            ->method('get')
            ->with($prerequisite::class)
            ->willReturn($prerequisite);

        $middleware = new PrerequisiteMiddleware(
            $responseFactory,
            $streamFactory,
            $container,
            [
                'POST:/foo' => [
                    $prerequisite::class,
                ],
            ],
        );

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testNotSatisfied(): void
    {
        $stream = $this->createMock(StreamInterface::class);

        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory
            ->expects(self::once())
            ->method('createStream')
            ->with(
                json_encode(
                    [
                        'message' => 'Prerequisite failed',
                        'code' => 'prerequisite.fooBar',
                    ],
                ),
            )
            ->willReturn($stream);

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

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $responseFactory
            ->expects(self::once())
            ->method('createResponse')
            ->with(409)
            ->willReturn($response);

        $uri = $this->createMock(UriInterface::class);
        $uri
            ->method('getPath')
            ->willReturn('/foo');

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getUri')
            ->willReturn($uri);

        $request
            ->method('getMethod')
            ->willReturn('POST');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects(self::never())
            ->method('handle');

        $prerequisite = $this
            ->getMockBuilder(PrerequisiteConstraint::class)
            ->setMockClassName('FooBar')
            ->getMock();
        $prerequisite
            ->expects(self::once())
            ->method('isSatisfied')
            ->with($request)
            ->willReturn(false);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects(self::once())
            ->method('get')
            ->with($prerequisite::class)
            ->willReturn($prerequisite);

        $middleware = new PrerequisiteMiddleware(
            $responseFactory,
            $streamFactory,
            $container,
            [
                'POST:/foo' => [
                    $prerequisite::class,
                ],
            ],
        );

        self::assertSame($response, $middleware->process($request, $handler));
    }
}
