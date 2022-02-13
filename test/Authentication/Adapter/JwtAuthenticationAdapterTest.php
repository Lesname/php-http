<?php
declare(strict_types=1);

namespace LessHttpTest\Authentication\Adapter;

use LessHttp\Authentication\Adapter\JwtAuthenticationAdapter;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @covers \LessHttp\Authentication\Adapter\JwtAuthenticationAdapter
 */
final class JwtAuthenticationAdapterTest extends TestCase
{
    public function testToken(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getHeaderLine')
            ->with('authorization')
            ->willReturn('Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6ImZpeiJ9.eyJzdWIiOiJmb28vMzhmMDY3MjItMWM3Mi00Y2VlLWIyMjUtYzBlODhhMDQzZTcyIn0.j2UdhnJvo8uI8d4_uUC72Wl10Vj6qXe_nTmV1a3TPCM');

        $adapter = new JwtAuthenticationAdapter(
            [
                'fiz' => [
                    'keyMaterial' => __DIR__ . '/keyMaterial.stub',
                    'algorithm' => 'HS256',
                ],
            ],
        );

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
            ->willReturn('Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6ImZpeiJ9.j2UdhnJvo8uI8d4_uUC72Wl10Vj6qXe_nTmV1a3TPCM');

        $adapter = new JwtAuthenticationAdapter(
            [
                'fiz' => [
                    'keyMaterial' => __DIR__ . '/keyMaterial.stub',
                    'algorithm' => 'HS256',
                ],
            ],
        );

        self::assertNull($adapter->resolve($request));
    }


    public function testTokenFailure(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getHeaderLine')
            ->with('authorization')
            ->willReturn('Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6ImZpeiJ9.eyJzdWIiOiJmb28vMzhmMDY3MjItMWM3Mi00Y2VlLWIyMjUtYzBlODhhMDQzZTcyIn0.j2UdhnJvo8uI8d4_uUC72Wl10Vj6qXe_nTmV1a3TPCM');

        $adapter = new JwtAuthenticationAdapter(
            [],
        );

        self::assertNull($adapter->resolve($request));
    }
}
