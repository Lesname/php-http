<?php
declare(strict_types=1);

namespace LessHttpTest\Prerequisite;

use LessHttp\Prerequisite\PrerequisiteMiddleware;
use LessHttp\Prerequisite\PrerequisiteMiddlewareFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * @covers \LessHttp\Prerequisite\PrerequisiteMiddlewareFactory
 */
final class PrerequisiteMiddlewareFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);

        $streamFactory = $this->createMock(StreamFactoryInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('get')
            ->willReturnMap(
                [
                    [
                        'config',
                        [
                            'routes' => [

                            ],
                        ],
                    ],
                    [
                        ResponseFactoryInterface::class,
                        $responseFactory,
                    ],
                    [
                        StreamFactoryInterface::class,
                        $streamFactory,
                    ]
                ],
            );

        $factory = new PrerequisiteMiddlewareFactory();
        $result = $factory($container);

        self::assertInstanceOf(PrerequisiteMiddleware::class, $result);
    }
}
