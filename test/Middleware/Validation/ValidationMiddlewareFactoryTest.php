<?php
declare(strict_types=1);

namespace LessHttpTest\Middleware\Validation;

use Psr\Log\LoggerInterface;
use LessDocumentor\Route\Input\RouteInputDocumentor;
use Symfony\Contracts\Translation\TranslatorInterface;
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
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $routeInputDocumentor = $this->createMock(RouteInputDocumentor::class);
        $translator = $this->createMock(TranslatorInterface::class);
        $logger = $this->createMock(LoggerInterface::class);
        $cache = $this->createMock(CacheInterface::class);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects(self::exactly(7))
            ->method('get')
            ->willReturnMap(
                [
                    [ResponseFactoryInterface::class, $responseFactory],
                    [StreamFactoryInterface::class, $streamFactory],
                    [RouteInputDocumentor::class, $routeInputDocumentor],
                    [TranslatorInterface::class, $translator],
                    [LoggerInterface::class, $logger],
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
