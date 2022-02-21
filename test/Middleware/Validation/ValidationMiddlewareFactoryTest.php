<?php
declare(strict_types=1);

namespace LessHttpTest\Middleware\Validation;

use LessDocumentor\Route\RouteDocumentor;
use LessHttp\Middleware\Validation\ValidationMiddleware;
use LessHttp\Middleware\Validation\ValidationMiddlewareFactory;
use LessValidator\Builder\RouteDocumentValidatorBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * @covers \LessHttp\Middleware\Validation\ValidationMiddlewareFactory
 */
final class ValidationMiddlewareFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $validatorBuilder = $this->createMock(RouteDocumentValidatorBuilder::class);
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $routeDocumentor = $this->createMock(RouteDocumentor::class);
        $cache = $this->createMock(CacheInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('get')
            ->willReturnMap(
                [
                    [RouteDocumentValidatorBuilder::class, $validatorBuilder],
                    [ResponseFactoryInterface::class, $responseFactory],
                    [StreamFactoryInterface::class, $streamFactory],
                    [RouteDocumentor::class, $routeDocumentor],
                    [CacheInterface::class, $cache],
                    [
                        'config',
                        [
                            'routes' => [],
                        ],
                    ],
                ],
            );

        $factory = new ValidationMiddlewareFactory();
        $result = $factory($container);

        self::assertInstanceOf(ValidationMiddleware::class, $result);
    }
}
