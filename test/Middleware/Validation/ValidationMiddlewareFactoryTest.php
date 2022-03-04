<?php
declare(strict_types=1);

namespace LessHttpTest\Middleware\Validation;

use LessDocumentor\Route\Input\RouteInputDocumentor;
use LessHttp\Middleware\Validation\ValidationMiddleware;
use LessHttp\Middleware\Validation\ValidationMiddlewareFactory;
use LessValidator\Builder\TypeDocumentValidatorBuilder;
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
        $validatorBuilder = $this->createMock(TypeDocumentValidatorBuilder::class);
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $routeInputDocumentor = $this->createMock(RouteInputDocumentor::class);
        $cache = $this->createMock(CacheInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects(self::exactly(6))
            ->method('get')
            ->willReturnMap(
                [
                    [TypeDocumentValidatorBuilder::class, $validatorBuilder],
                    [ResponseFactoryInterface::class, $responseFactory],
                    [StreamFactoryInterface::class, $streamFactory],
                    [RouteInputDocumentor::class, $routeInputDocumentor],
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
