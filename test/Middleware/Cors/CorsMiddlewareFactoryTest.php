<?php
declare(strict_types=1);

namespace LesHttpTest\Middleware\Cors;

use LesHttp\Middleware\Cors\CorsMiddleware;
use LesHttp\Middleware\Cors\CorsMiddlewareFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

/**
 * @covers \LesHttp\Middleware\Cors\CorsMiddlewareFactory
 */
final class CorsMiddlewareFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $config = [
            'cors' => [
                'default' => [
                    'origins' => ['https://foo.bar'],
                    'methods' => ['fiz'],
                    'headers' => ['abc'],
                    'maxAge' => 86400,
                ],
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
