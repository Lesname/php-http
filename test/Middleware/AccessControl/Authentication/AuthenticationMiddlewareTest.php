<?php

declare(strict_types=1);

namespace LesHttpTest\Middleware\AccessControl\Authentication;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use LesValueObject\Composite\ForeignReference;
use LesValueObject\Composite\DynamicCompositeValueObject;
use LesHttp\Middleware\AccessControl\Authentication\Adapter\Identity;
use LesHttp\Middleware\AccessControl\Authentication\AuthenticationMiddleware;
use LesHttp\Middleware\AccessControl\Authentication\Adapter\AuthenticationAdapter;

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

        $middleware = new AuthenticationMiddleware([$adapter]);

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testResolve(): void
    {
        $context = new DynamicCompositeValueObject([]);
        $reference = ForeignReference::fromString('abc/9cd78005-5c15-40a3-8dd5-6836cee2ee81');

        $identity = new Identity($reference, $context);

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects(self::exactly(3))
            ->method('withAttribute')
            ->willReturnMap(
                [
                    ['identity', $identity->reference],
                    ['identity.reference', $identity->reference],
                    ['identity.context', $identity->context],
                ],
            )
            ->willReturn($request);

        $adapter = $this->createMock(AuthenticationAdapter::class);
        $adapter
            ->expects(self::once())
            ->method('resolve')
            ->with($request)
            ->willReturn($identity);

        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $middleware = new AuthenticationMiddleware([$adapter]);

        self::assertSame($response, $middleware->process($request, $handler));
    }
}
