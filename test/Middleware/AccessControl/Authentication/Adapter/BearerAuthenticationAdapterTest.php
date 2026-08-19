<?php

declare(strict_types=1);

namespace LesHttpTest\Middleware\AccessControl\Authentication\Adapter;

use LesToken\Codec\TokenCodec;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use LesValueObject\Composite\DynamicCompositeValueObject;
use LesHttp\Middleware\AccessControl\Authentication\Adapter\BearerAuthenticationAdapter;

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
                ['sub' => 'foo/38f06722-1c72-4cee-b225-c0e88a043e72'],
            );


        $adapter = new BearerAuthenticationAdapter($coded);

        $result = $adapter->resolve($request);

        $identity = $result->reference;

        self::assertSame('38f06722-1c72-4cee-b225-c0e88a043e72', (string)$identity->id);
        self::assertSame('foo', (string)$identity->type);

        self::assertEquals(
            new DynamicCompositeValueObject(['sub' => 'foo/38f06722-1c72-4cee-b225-c0e88a043e72']),
            $result->context,
        );
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
