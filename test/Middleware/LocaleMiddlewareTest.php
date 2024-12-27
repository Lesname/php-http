<?php
declare(strict_types=1);

namespace LessHttpTest\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use LessHttp\Middleware\Locale\LocaleMiddleware;

/**
 * @covers \LessHttp\Middleware\Locale\LocaleMiddleware
 */
class LocaleMiddlewareTest extends TestCase
{
    public function testAddedExactMatchLocale(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getHeaderLine')
            ->with('Accept-Language')
            ->willReturn('en;q=0.5,en-GB;q=0.7,en-US;q=1,nl');

        $request
            ->expects(self::once())
            ->method('withAttribute')
            ->with('useLocale', 'en_US')
            ->willReturn($request);

        $middleware = new LocaleMiddleware(
            'fr_FR',
            ['en_US', 'de_DE'],
        );

        $handler = $this->createMock(RequestHandlerInterface::class);

        $middleware->process($request, $handler);
    }

    public function testAddedCloseMatchLocale(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getHeaderLine')
            ->with('Accept-Language')
            ->willReturn('en;q=0.5,en-GB;q=0.7,q=1,nl');

        $request
            ->expects(self::once())
            ->method('withAttribute')
            ->with('useLocale', 'en_US')
            ->willReturn($request);

        $middleware = new LocaleMiddleware(
            'fr_FR',
            ['en_US', 'de_DE'],
        );

        $handler = $this->createMock(RequestHandlerInterface::class);

        $middleware->process($request, $handler);
    }

    public function testAddedDefaultLocale(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getHeaderLine')
            ->with('Accept-Language')
            ->willReturn('nl');

        $request
            ->expects(self::once())
            ->method('withAttribute')
            ->with('useLocale', 'fr_FR')
            ->willReturn($request);

        $middleware = new LocaleMiddleware(
            'fr_FR',
            ['en_US', 'de_DE'],
        );

        $handler = $this->createMock(RequestHandlerInterface::class);

        $middleware->process($request, $handler);
    }
}
