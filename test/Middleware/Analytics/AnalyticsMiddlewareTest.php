<?php
declare(strict_types=1);

namespace LessHttpTest\Middleware\Analytics;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Exception;
use LessHttp\Middleware\Analytics\AnalyticsMiddleware;
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
        $startTime = microtime(true);

        $builder = $this->createMock(QueryBuilder::class);

        $builder
            ->expects(self::exactly(9))
            ->method('setParameter')
            ->withConsecutive(
                [':service', 'fiz'],
                [':action', 'foo.bar'],
                [':identity', 'abc/def'],
                [':ip', '127.0.0.1'],
                [':user_agent', 'local'],
                [':requested_on', (int)floor($startTime * 1000)],
                [
                    ':duration',
                    new Callback(
                        function ($value) use ($startTime) {
                            $diff = (int)floor((microtime(true) - $startTime) * 1000);

                            return $diff - $value <= 2;
                        }
                    ),
                ],
                [':response', 200],
                [':error', null],
            )
            ->willReturn($builder);

        $builder
            ->expects(self::exactly(9))
            ->method('setValue')
            ->withConsecutive(
                ['`service`', ':service'],
                ['`action`', ':action'],
                ['`identity`', ':identity'],
                ['`ip`', ':ip'],
                ['`user_agent`', ':user_agent'],
                ['`requested_on`', ':requested_on'],
                ['`duration`', ':duration'],
                ['`response`', ':response'],
                ['`error`', ':error'],
            )
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
                    ['identity', null, 'abc/def'],
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

        $middleware = new AnalyticsMiddleware($connection, 'fiz');

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testHandlePostErrorWithJson(): void
    {
        $startTime = microtime(true);

        $builder = $this->createMock(QueryBuilder::class);

        $builder
            ->expects(self::exactly(9))
            ->method('setParameter')
            ->withConsecutive(
                [':service', 'fiz'],
                [':action', 'foo.bar'],
                [':identity', 'abc/def'],
                [':ip', '127.0.0.1'],
                [':user_agent', 'local'],
                [':requested_on', (int)floor($startTime * 1000)],
                [
                    ':duration',
                    new Callback(
                        function ($value) use ($startTime) {
                            $diff = (int)floor((microtime(true) - $startTime) * 1000);

                            return $diff - $value <= 2;
                        },
                    ),
                ],
                [':response', 422],
                [':error', '{"fiz":"biz"}'],
            )
            ->willReturn($builder);

        $builder
            ->expects(self::exactly(9))
            ->method('setValue')
            ->withConsecutive(
                ['`service`', ':service'],
                ['`action`', ':action'],
                ['`identity`', ':identity'],
                ['`ip`', ':ip'],
                ['`user_agent`', ':user_agent'],
                ['`requested_on`', ':requested_on'],
                ['`duration`', ':duration'],
                ['`response`', ':response'],
                ['`error`', ':error'],
            )
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
                    ['identity', null, 'abc/def'],
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

        $middleware = new AnalyticsMiddleware($connection, 'fiz');

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testHandlePostErrorWithoutJson(): void
    {
        $startTime = microtime(true);

        $builder = $this->createMock(QueryBuilder::class);
        $builder
            ->expects(self::exactly(9))
            ->method('setParameter')
            ->withConsecutive(
                [':service', 'fiz'],
                [':action', 'foo.bar'],
                [':identity', 'abc/def'],
                [':ip', '127.0.0.1'],
                [':user_agent', 'local'],
                [':requested_on', (int)floor($startTime * 1000)],
                [
                    ':duration',
                    new Callback(
                        function (int $value) use ($startTime) {
                            $diff = (int)floor((microtime(true) - $startTime) * 1000);

                            return ($diff - $value) <= 2;
                        },
                    ),
                ],
                [':response', 422],
                [':error', '"fiz"'],
            )
            ->willReturn($builder);

        $builder
            ->expects(self::exactly(9))
            ->method('setValue')
            ->withConsecutive(
                ['`service`', ':service'],
                ['`action`', ':action'],
                ['`identity`', ':identity'],
                ['`ip`', ':ip'],
                ['`user_agent`', ':user_agent'],
                ['`requested_on`', ':requested_on'],
                ['`duration`', ':duration'],
                ['`response`', ':response'],
                ['`error`', ':error'],
            )
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
                    ['identity', null, 'abc/def'],
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

        $middleware = new AnalyticsMiddleware($connection, 'fiz');

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testHandlePostThrowable(): void
    {
        $this->expectException(Throwable::class);

        $startTime = microtime(true);

        $builder = $this->createMock(QueryBuilder::class);

        $e = new Exception('Fiz biz');

        $builder
            ->expects(self::exactly(9))
            ->method('setParameter')
            ->withConsecutive(
                [':service', 'fiz'],
                [':action', 'foo.bar'],
                [':identity', 'abc/def'],
                [':ip', '127.0.0.1'],
                [':user_agent', 'local'],
                [':requested_on', (int)floor($startTime * 1000)],
                [
                    ':duration',
                    new Callback(
                        function (int $value) use ($startTime) {
                            $diff = (int)floor((microtime(true) - $startTime) * 1000);

                            return ($diff - $value) <= 2;
                        },
                    ),
                ],
                [':response', 500],
                [':error', '{"throwable":"Fiz biz"}'],
            )
            ->willReturn($builder);

        $builder
            ->expects(self::exactly(9))
            ->method('setValue')
            ->withConsecutive(
                ['`service`', ':service'],
                ['`action`', ':action'],
                ['`identity`', ':identity'],
                ['`ip`', ':ip'],
                ['`user_agent`', ':user_agent'],
                ['`requested_on`', ':requested_on'],
                ['`duration`', ':duration'],
                ['`response`', ':response'],
                ['`error`', ':error'],
            )
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
                    ['identity', null, 'abc/def'],
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

        $middleware = new AnalyticsMiddleware($connection, 'fiz');

        $middleware->process($request, $handler);
    }
}
