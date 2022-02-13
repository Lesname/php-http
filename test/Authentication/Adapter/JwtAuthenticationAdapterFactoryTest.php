<?php
declare(strict_types=1);

namespace LessHttpTest\Authentication\Adapter;

use LessHttp\Authentication\Adapter\JwtAuthenticationAdapter;
use LessHttp\Authentication\Adapter\JwtAuthenticationAdapterFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \LessHttp\Authentication\Adapter\JwtAuthenticationAdapterFactory
 */
final class JwtAuthenticationAdapterFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $config = [
            'jwt' => [
                'keys' => [
                    'fiz' => [
                        'keyMaterial' => '',
                        'algorith' => 'RS512',
                    ]
                ],
            ],
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $factory = new JwtAuthenticationAdapterFactory();
        $result = $factory($container);

        self::assertInstanceOf(JwtAuthenticationAdapter::class, $result);
    }
}
