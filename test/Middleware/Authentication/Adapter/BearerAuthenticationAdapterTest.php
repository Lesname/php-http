<?php
declare(strict_types=1);

namespace LessHttpTest\Middleware\Authentication\Adapter;

use LessHttp\Middleware\Authentication\Adapter\BearerAuthenticationAdapter;
use LessHttp\Middleware\Authentication\Adapter\JwtAuthenticationAdapter;
use LessToken\Codec\JwtTokenCodec;
use LessToken\Codec\TokenCodec;
use LessToken\Signer\HmacSigner;
use LessToken\Signer\Key\FileKey;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @covers \LessHttp\Middleware\Authentication\Adapter\BearerAuthenticationAdapter
 */
final class BearerAuthenticationAdapterTest extends TestCase
{
    public function testToken(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getHeaderLine')
            ->with('authorization')
            ->willReturn('Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6ImZpeiJ9.eyJzdWIiOiJmb28vMzhmMDY3MjItMWM3Mi00Y2VlLWIyMjUtYzBlODhhMDQzZTcyIn0.j2UdhnJvo8uI8d4_uUC72Wl10Vj6qXe_nTmV1a3TPCM');

        $coded = $this->createMock(TokenCodec::class);
        $coded
            ->expects(self::once())
            ->method('decode')
            ->with('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6ImZpeiJ9.eyJzdWIiOiJmb28vMzhmMDY3MjItMWM3Mi00Y2VlLWIyMjUtYzBlODhhMDQzZTcyIn0.j2UdhnJvo8uI8d4_uUC72Wl10Vj6qXe_nTmV1a3TPCM')
            ->willReturn(
                ['identity' => 'foo/38f06722-1c72-4cee-b225-c0e88a043e72'],
            );


        $adapter = new BearerAuthenticationAdapter($coded);

        $result = $adapter->resolve($request);

        self::assertSame('38f06722-1c72-4cee-b225-c0e88a043e72', (string)$result->id);
        self::assertSame('foo', (string)$result->type);
    }

    public function testTokenNoHeader(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getHeaderLine')
            ->with('authorization')
            ->willReturn('Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6ImZpeiJ9.eyJzdWIiOiJmb28vMzhmMDY3MjItMWM3Mi00Y2VlLWIyMjUtYzBlODhhMDQzZTcyIn0.j2UdhnJvo8uI8d4_uUC72Wl10Vj6qXe_nTmV1a3TPCM');

        $coded = $this->createMock(TokenCodec::class);
        $coded
            ->expects(self::once())
            ->method('decode')
            ->with('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6ImZpeiJ9.eyJzdWIiOiJmb28vMzhmMDY3MjItMWM3Mi00Y2VlLWIyMjUtYzBlODhhMDQzZTcyIn0.j2UdhnJvo8uI8d4_uUC72Wl10Vj6qXe_nTmV1a3TPCM')
            ->willReturn(null);

        $adapter = new BearerAuthenticationAdapter($coded);

        self::assertNull($adapter->resolve($request));
    }
}
