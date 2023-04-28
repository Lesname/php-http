<?php
declare(strict_types=1);

namespace LessHttpTest\Middleware\Throttle;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Exception;
use LessHttp\Middleware\Throttle\ThrottleMiddleware;
use LessValueObject\Composite\ForeignReference;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

/**
 * @covers \LessHttp\Middleware\Throttle\ThrottleMiddleware
 */
final class ThrottleMiddlewareTest extends TestCase
{
    public function testIsThrottled(): void
    {
        $stream = $this->createMock(StreamInterface::class);

        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory
            ->expects(self::once())
            ->method('createStream')
            ->willReturn($stream);

        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects(self::once())
            ->method('withBody')
            ->with($stream)
            ->willReturn($response);
        $response
            ->expects(self::once())
            ->method('withAddedHeader')
            ->with('content-type', 'application/json')
            ->willReturn($response);

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $responseFactory
            ->expects(self::once())
            ->method('createResponse')
            ->with(429)
            ->willReturn($response);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder
            ->expects(self::once())
            ->method('select')
            ->willReturn($queryBuilder);

        $queryBuilder
            ->expects(self::once())
            ->method('from')
            ->with('throttle_request')
            ->willReturn($queryBuilder);

        $queryBuilder
            ->expects(self::exactly(2))
            ->method('andWhere')
            ->withConsecutive(
                ['identity = :identity'],
                ['requested_on >= :since'],
            )
            ->willReturn($queryBuilder);

        $queryBuilder
            ->expects(self::exactly(2))
            ->method('setParameter')
            ->withConsecutive(
                ['identity', 'bar/b53f8a97-25f4-49c4-9d30-dc70124e8877'],
                ['since'],
            )
            ->willReturn($queryBuilder);

        $queryBuilder
            ->expects(self::once())
            ->method('fetchOne')
            ->willReturn('432');

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $limits = [
            [
                'duration' => 999_999,
                'points' => 321,
            ],
        ];

        $uri = $this->createMock(UriInterface::class);
        $uri
            ->method('getPath')
            ->willReturn('/fiz/bar');

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getUri')
            ->willReturn($uri);

        $request
            ->method('getAttribute')
            ->with('identity')
            ->willReturn(ForeignReference::fromString('bar/b53f8a97-25f4-49c4-9d30-dc70124e8877'));

        $request
            ->method('getServerParams')
            ->willReturn(['REMOTE_ADDR' => '127.0.0.1']);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects(self::never())
            ->method('handle');

        $middleware = new ThrottleMiddleware($responseFactory, $streamFactory, $connection, $limits);

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testIsNotThrottled(): void
    {
        $streamFactory = $this->createMock(StreamFactoryInterface::class);

        $response = $this->createMock(ResponseInterface::class);

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $responseFactory
            ->expects(self::never())
            ->method('createResponse');

        $selectQueryBuilder = $this->createMock(QueryBuilder::class);
        $selectQueryBuilder
            ->expects(self::once())
            ->method('select')
            ->willReturn($selectQueryBuilder);

        $selectQueryBuilder
            ->expects(self::once())
            ->method('from')
            ->with('throttle_request')
            ->willReturn($selectQueryBuilder);

        $selectQueryBuilder
            ->expects(self::exactly(2))
            ->method('andWhere')
            ->withConsecutive(
                ['ip = :ip'],
                ['requested_on >= :since'],
            )
            ->willReturn($selectQueryBuilder);

        $selectQueryBuilder
            ->expects(self::exactly(2))
            ->method('setParameter')
            ->withConsecutive(
                ['ip', '127.0.0.1'],
                ['since'],
            )
            ->willReturn($selectQueryBuilder);

        $selectQueryBuilder
            ->expects(self::once())
            ->method('fetchOne')
            ->willReturn('123');

        $insertQueryBuilder = $this->createMock(QueryBuilder::class);
        $insertQueryBuilder
            ->expects(self::once())
            ->method('insert')
            ->with('throttle_request')
            ->willReturn($insertQueryBuilder);

        $insertQueryBuilder
            ->expects(self::exactly(5))
            ->method('setParameter')
            ->willReturn($insertQueryBuilder);

        $insertQueryBuilder
            ->expects(self::once())
            ->method('executeStatement')
            ->willReturn(1);

        $insertQueryBuilder
            ->expects(self::exactly(5))
            ->method('setValue')
            ->willReturn($insertQueryBuilder);

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects(self::exactly(2))
            ->method('createQueryBuilder')
            ->willReturnOnConsecutiveCalls($selectQueryBuilder, $insertQueryBuilder);

        $limits = [
            [
                'duration' => 999_999,
                'points' => 321,
            ],
        ];

        $uri = $this->createMock(UriInterface::class);
        $uri
            ->method('getPath')
            ->willReturn('bar');

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getUri')
            ->willReturn($uri);

        $request
            ->method('getAttribute')
            ->with('identity')
            ->willReturn(null);

        $request
            ->method('getServerParams')
            ->willReturn(['REMOTE_ADDR' => '127.0.0.1']);

        $request
            ->method('getMethod')
            ->willReturn('POST');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $middleware = new ThrottleMiddleware($responseFactory, $streamFactory, $connection, $limits);

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testOptionsNotLogged(): void
    {
        $streamFactory = $this->createMock(StreamFactoryInterface::class);

        $response = $this->createMock(ResponseInterface::class);

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $responseFactory
            ->expects(self::never())
            ->method('createResponse');

        $selectQueryBuilder = $this->createMock(QueryBuilder::class);
        $selectQueryBuilder
            ->expects(self::once())
            ->method('select')
            ->willReturn($selectQueryBuilder);

        $selectQueryBuilder
            ->expects(self::once())
            ->method('from')
            ->with('throttle_request')
            ->willReturn($selectQueryBuilder);

        $selectQueryBuilder
            ->expects(self::exactly(2))
            ->method('andWhere')
            ->withConsecutive(
                ['ip = :ip'],
                ['requested_on >= :since'],
            )
            ->willReturn($selectQueryBuilder);

        $selectQueryBuilder
            ->expects(self::exactly(2))
            ->method('setParameter')
            ->withConsecutive(
                ['ip', '127.0.0.1'],
                ['since'],
            )
            ->willReturn($selectQueryBuilder);

        $selectQueryBuilder
            ->expects(self::once())
            ->method('fetchOne')
            ->willReturn('123');

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturnOnConsecutiveCalls($selectQueryBuilder);

        $limits = [
            [
                'duration' => 999_999,
                'points' => 321,
            ],
        ];

        $uri = $this->createMock(UriInterface::class);
        $uri
            ->method('getPath')
            ->willReturn('bar');

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getUri')
            ->willReturn($uri);

        $request
            ->method('getAttribute')
            ->with('identity')
            ->willReturn(null);

        $request
            ->method('getServerParams')
            ->willReturn(['REMOTE_ADDR' => '127.0.0.1']);

        $request
            ->method('getMethod')
            ->willReturn('OPTIONS');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $middleware = new ThrottleMiddleware($responseFactory, $streamFactory, $connection, $limits);

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testIsNotThrottledThrowable(): void
    {
        $this->expectException(Throwable::class);

        $e = new class extends Exception {
        };

        $streamFactory = $this->createMock(StreamFactoryInterface::class);

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $responseFactory
            ->expects(self::never())
            ->method('createResponse');

        $selectQueryBuilder = $this->createMock(QueryBuilder::class);
        $selectQueryBuilder
            ->expects(self::once())
            ->method('select')
            ->willReturn($selectQueryBuilder);

        $selectQueryBuilder
            ->expects(self::once())
            ->method('from')
            ->with('throttle_request')
            ->willReturn($selectQueryBuilder);

        $selectQueryBuilder
            ->expects(self::exactly(2))
            ->method('andWhere')
            ->with()
            ->withConsecutive(
                ['identity = :identity'],
                ['requested_on >= :since'],
            )
            ->willReturn($selectQueryBuilder);

        $selectQueryBuilder
            ->expects(self::exactly(2))
            ->method('setParameter')
            ->withConsecutive(
                ['identity', 'bar/b53f8a97-25f4-49c4-9d30-dc70124e8877'],
                ['since'],
            )
            ->willReturn($selectQueryBuilder);

        $selectQueryBuilder
            ->expects(self::once())
            ->method('fetchOne')
            ->willReturn('123');

        $insertQueryBuilder = $this->createMock(QueryBuilder::class);
        $insertQueryBuilder
            ->expects(self::once())
            ->method('insert')
            ->with('throttle_request')
            ->willReturn($insertQueryBuilder);

        $insertQueryBuilder
            ->expects(self::exactly(5))
            ->method('setParameter')
            ->willReturn($insertQueryBuilder);

        $insertQueryBuilder
            ->expects(self::once())
            ->method('executeStatement')
            ->willReturn(1);

        $insertQueryBuilder
            ->expects(self::exactly(5))
            ->method('setValue')
            ->willReturn($insertQueryBuilder);

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects(self::exactly(2))
            ->method('createQueryBuilder')
            ->willReturnOnConsecutiveCalls($selectQueryBuilder, $insertQueryBuilder);

        $limits = [
            [
                'duration' => 999_999,
                'points' => 321,
            ],
        ];

        $uri = $this->createMock(UriInterface::class);
        $uri
            ->method('getPath')
            ->willReturn('bar');

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getUri')
            ->willReturn($uri);

        $request
            ->method('getAttribute')
            ->with('identity')
            ->willReturn(ForeignReference::fromString('bar/b53f8a97-25f4-49c4-9d30-dc70124e8877'));

        $request
            ->method('getServerParams')
            ->willReturn(['REMOTE_ADDR' => '127.0.0.1']);

        $request
            ->method('getMethod')
            ->willReturn('POST');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willThrowException($e);

        $middleware = new ThrottleMiddleware($responseFactory, $streamFactory, $connection, $limits);
        $middleware->process($request, $handler);
    }
}
