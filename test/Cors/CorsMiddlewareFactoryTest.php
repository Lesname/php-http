<?php
declare(strict_types=1);

namespace LessHttpTest\Cors;

use LessHttp\Cors\CorsMiddleware;
use LessHttp\Cors\CorsMiddlewareFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

/**
 * @covers \LessHttp\Cors\CorsMiddlewareFactory
 */
final class CorsMiddlewareFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $config = [
            'cors' => [
                'origins' => ['https://foo.bar'],
                'methods' => ['fiz'],
                'headers' => ['abc'],
                'maxAge' => 86400,
            ],
        ];

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('get')
            ->willReturnMap(
                [
                    [ResponseFactoryInterface::class, $responseFactory],
                    ['config', $config],
                ],
            );

        $factory = new CorsMiddlewareFactory();
        $result = $factory($container);

        self::assertInstanceOf(CorsMiddleware::class, $result);
    }
}
