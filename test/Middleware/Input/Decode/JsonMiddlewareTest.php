<?php
declare(strict_types=1);

namespace LesHttpTest\Middleware\Input\Decode;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use LesHttp\Middleware\Input\Decode\JsonMiddleware;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(JsonMiddleware::class)]
class JsonMiddlewareTest extends TestCase
{
    public function testIgnoreGet(): void
    {
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);

        $streamFactory = $this->createMock(StreamFactoryInterface::class);

        $middleware = new JsonMiddleware($responseFactory, $streamFactory);

        $response = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects(self::once())
            ->method('getMethod')
            ->willReturn('GET');

        $request->expects(self::never())->method('getHeaderLine');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testIgnoreNonJsonInput(): void
    {
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);

        $streamFactory = $this->createMock(StreamFactoryInterface::class);

        $middleware = new JsonMiddleware($responseFactory, $streamFactory);

        $response = $this->createMock(ResponseInterface::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects(self::once())
            ->method('getMethod')
            ->willReturn('POST');

        $request
            ->expects(self::once())
            ->method('getHeaderLine')
            ->with('Content-Type')
            ->willReturn('application/xml');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testMalformedJson(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects(self::once())
            ->method('withHeader')
            ->with('content-type', 'application/json')
            ->willReturn($response);

        $response
            ->expects(self::once())
            ->method('withBody')
            ->willReturn($response);

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $responseFactory
            ->expects(self::once())
            ->method('createResponse')
            ->with(400)
            ->willReturn($response);

        $streamFactory = $this->createMock(StreamFactoryInterface::class);

        $middleware = new JsonMiddleware($responseFactory, $streamFactory);

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects(self::once())
            ->method('getMethod')
            ->willReturn('POST');

        $request
            ->expects(self::once())
            ->method('getHeaderLine')
            ->with('Content-Type')
            ->willReturn('application/json');

        $stream = $this->createMock(\Psr\Http\Message\StreamInterface::class);
        $stream
            ->method('getContents')
            ->willReturn('{"foo":');

        $request
            ->method('getBody')
            ->willReturn($stream);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::never())->method('handle');

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testNonJsonArray(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects(self::once())
            ->method('withHeader')
            ->with('content-type', 'application/json')
            ->willReturn($response);

        $response
            ->expects(self::once())
            ->method('withBody')
            ->willReturn($response);

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $responseFactory
            ->expects(self::once())
            ->method('createResponse')
            ->with(429)
            ->willReturn($response);

        $streamFactory = $this->createMock(StreamFactoryInterface::class);

        $middleware = new JsonMiddleware($responseFactory, $streamFactory);

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects(self::once())
            ->method('getMethod')
            ->willReturn('POST');

        $request
            ->expects(self::once())
            ->method('getHeaderLine')
            ->with('Content-Type')
            ->willReturn('application/json');

        $stream = $this->createMock(\Psr\Http\Message\StreamInterface::class);
        $stream
            ->method('getContents')
            ->willReturn('1');

        $request
            ->method('getBody')
            ->willReturn($stream);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::never())->method('handle');

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testValidJson(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $responseFactory->expects(self::never())->method('createResponse');

        $streamFactory = $this->createMock(StreamFactoryInterface::class);

        $middleware = new JsonMiddleware($responseFactory, $streamFactory);

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects(self::once())
            ->method('getMethod')
            ->willReturn('POST');

        $request
            ->expects(self::once())
            ->method('withParsedBody')
            ->with([])
            ->willReturn($request);

        $request
            ->expects(self::once())
            ->method('getHeaderLine')
            ->with('Content-Type')
            ->willReturn('application/json');

        $stream = $this->createMock(\Psr\Http\Message\StreamInterface::class);
        $stream
            ->method('getContents')
            ->willReturn('{}');

        $request
            ->method('getBody')
            ->willReturn($stream);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        self::assertSame($response, $middleware->process($request, $handler));
    }
}
