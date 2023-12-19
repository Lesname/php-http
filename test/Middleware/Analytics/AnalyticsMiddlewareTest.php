<?php
declare(strict_types=1);

namespace LessHttpTest\Middleware\Analytics;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Exception;
use LessDatabase\Query\Builder\Helper\LabelHelper;
use LessHttp\Middleware\Analytics\AnalyticsMiddleware;
use LessValueObject\Composite\ForeignReference;
use LessValueObject\Number\Int\Date\MilliTimestamp;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

/**
 * @covers \LessHttp\Middleware\Analytics\AnalyticsMiddleware
 */
final class AnalyticsMiddlewareTest extends TestCase
{
    public function testHandleOptions(): void
    {
        $connection = $this->createMock(Connection::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects(self::once())
            ->method('getMethod')
            ->willReturn('OPTIONS');

        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);

        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $middleware = new AnalyticsMiddleware($connection, 'fiz');

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testHandleHead(): void
    {
        $connection = $this->createMock(Connection::class);

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects(self::once())
            ->method('getMethod')
            ->willReturn('HEAD');

        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);

        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $middleware = new AnalyticsMiddleware($connection, 'fiz');

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testHandlePostSuccess(): void
    {
        $startTime = 123.456;
        $now = new MilliTimestamp(654_321);

        $builder = $this->createMock(QueryBuilder::class);

        $builder
            ->expects(self::exactly(9))
            ->method('setParameter')
            ->willReturn($builder);

        $builder
            ->expects(self::exactly(9))
            ->method('setValue')
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('insert')
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('executeStatement')
            ->willReturn(1);

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($builder);

        $uri = $this->createMock(UriInterface::class);
        $uri
            ->method('getPath')
            ->willReturn('/fiz/foo.bar');

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getMethod')
            ->willReturn('POST');

        $request
            ->method('getServerParams')
            ->willReturn(
                [
                    'REMOTE_ADDR' => '127.0.0.1',
                    'REQUEST_TIME_FLOAT' => $startTime,
                ],
            );

        $request
            ->method('getHeaderLine')
            ->willReturnMap(
                [
                    ['user-agent', 'local'],
                ],
            );

        $request
            ->method('getAttribute')
            ->willReturnMap(
                [
                    ['identity', null, ForeignReference::fromString('abc/707cfbb1-e06d-4635-a7cf-7f4c774d67d6')],
                    ['claims', null, ['rol' => 'foo']],
                ],
            );

        $request
            ->method('getUri')
            ->willReturn($uri);

        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects(self::once())
            ->method('getStatusCode')
            ->willReturn(200);

        $handler = $this->createMock(RequestHandlerInterface::class);

        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $middleware = new AnalyticsMiddleware($connection, 'fiz', $now);

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testHandlePostErrorWithJson(): void
    {
        $startTime = 123.456;
        $now = new MilliTimestamp(654_321);

        $builder = $this->createMock(QueryBuilder::class);

        $builder
            ->expects(self::exactly(9))
            ->method('setParameter')
            ->willReturn($builder);

        $builder
            ->expects(self::exactly(9))
            ->method('setValue')
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('insert')
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('executeStatement')
            ->willReturn(1);

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($builder);

        $uri = $this->createMock(UriInterface::class);
        $uri
            ->method('getPath')
            ->willReturn('/fiz/foo.bar');

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getMethod')
            ->willReturn('POST');

        $request
            ->method('getServerParams')
            ->willReturn(
                [
                    'REMOTE_ADDR' => '127.0.0.1',
                    'REQUEST_TIME_FLOAT' => $startTime,
                ],
            );

        $request
            ->method('getHeaderLine')
            ->willReturnMap(
                [
                    ['user-agent', 'local'],
                ],
            );

        $request
            ->method('getAttribute')
            ->willReturnMap(
                [
                    ['identity', null, ForeignReference::fromString('abc/707cfbb1-e06d-4635-a7cf-7f4c774d67d6')],
                    ['claims', null, ['rol' => 'foo']],
                ],
            );

        $request
            ->method('getUri')
            ->willReturn($uri);

        $body = $this->createMock(StreamInterface::class);
        $body
            ->method('__toString')
            ->willReturn('{"fiz":"biz"}');

        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects(self::once())
            ->method('getStatusCode')
            ->willReturn(422);

        $response
            ->method('getHeaderLine')
            ->with('content-type')
            ->willReturn('application/json');

        $response
            ->expects(self::once())
            ->method('getBody')
            ->willReturn($body);

        $handler = $this->createMock(RequestHandlerInterface::class);

        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $middleware = new AnalyticsMiddleware($connection, 'fiz', $now);

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testHandlePostErrorWithoutJson(): void
    {
        $startTime = 123.456;
        $now = new MilliTimestamp(654_321);

        $builder = $this->createMock(QueryBuilder::class);
        $builder
            ->expects(self::exactly(9))
            ->method('setParameter')
            ->willReturn($builder);

        $builder
            ->expects(self::exactly(9))
            ->method('setValue')
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('insert')
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('executeStatement')
            ->willReturn(1);

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($builder);

        $uri = $this->createMock(UriInterface::class);
        $uri
            ->method('getPath')
            ->willReturn('/fiz/foo.bar');

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getMethod')
            ->willReturn('POST');

        $request
            ->method('getServerParams')
            ->willReturn(
                [
                    'REMOTE_ADDR' => '127.0.0.1',
                    'REQUEST_TIME_FLOAT' => $startTime,
                ],
            );

        $request
            ->method('getHeaderLine')
            ->willReturnMap(
                [
                    ['user-agent', 'local'],
                ],
            );

        $request
            ->method('getAttribute')
            ->willReturnMap(
                [
                    ['identity', null, ForeignReference::fromString('abc/707cfbb1-e06d-4635-a7cf-7f4c774d67d6')],
                    ['claims', null, null],
                ],
            );

        $request
            ->method('getUri')
            ->willReturn($uri);

        $body = $this->createMock(StreamInterface::class);
        $body
            ->method('__toString')
            ->willReturn('fiz');

        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects(self::once())
            ->method('getStatusCode')
            ->willReturn(422);

        $response
            ->method('getHeaderLine')
            ->with('content-type')
            ->willReturn('text/plain');

        $response
            ->expects(self::once())
            ->method('getBody')
            ->willReturn($body);

        $handler = $this->createMock(RequestHandlerInterface::class);

        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $middleware = new AnalyticsMiddleware($connection, 'fiz', $now);

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testHandlePostThrowable(): void
    {
        $this->expectException(Throwable::class);

        $startTime = 123.456;
        $now = new MilliTimestamp(654_321);

        $builder = $this->createMock(QueryBuilder::class);

        $e = new Exception('Fiz biz');

        $error = json_encode(
            [
                'throwable' => $e->getMessage(),
                'class' => get_debug_type($e),
            ],
            JSON_THROW_ON_ERROR,
        );

        $builder
            ->expects(self::exactly(9))
            ->method('setParameter')
            ->willReturn($builder);

        $builder
            ->expects(self::exactly(9))
            ->method('setValue')
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('insert')
            ->willReturn($builder);

        $builder
            ->expects(self::once())
            ->method('executeStatement')
            ->willReturn(1);

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($builder);

        $uri = $this->createMock(UriInterface::class);
        $uri
            ->method('getPath')
            ->willReturn('foo.bar');

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getMethod')
            ->willReturn('POST');

        $request
            ->method('getServerParams')
            ->willReturn(
                [
                    'REMOTE_ADDR' => '127.0.0.1',
                    'REQUEST_TIME_FLOAT' => $startTime,
                ],
            );

        $request
            ->method('getHeaderLine')
            ->willReturnMap(
                [
                    ['user-agent', 'local'],
                ],
            );

        $request
            ->method('getAttribute')
            ->willReturnMap(
                [
                    ['identity', null, ForeignReference::fromString('abc/707cfbb1-e06d-4635-a7cf-7f4c774d67d6')],
                    ['claims', null, null],
                ],
            );

        $request
            ->method('getUri')
            ->willReturn($uri);

        $handler = $this->createMock(RequestHandlerInterface::class);

        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willThrowException($e);

        $middleware = new AnalyticsMiddleware($connection, 'fiz', $now);

        $middleware->process($request, $handler);
    }
}
