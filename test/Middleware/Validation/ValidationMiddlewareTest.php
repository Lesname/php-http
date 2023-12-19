<?php
declare(strict_types=1);

namespace LessHttpTest\Middleware\Validation;

use Psr\Log\LoggerInterface;
use LessDocumentor\Route\Input\RouteInputDocumentor;
use LessDocumentor\Type\Document\TypeDocument;
use LessValidator\ValidateResult\ErrorValidateResult;
use Symfony\Contracts\Translation\TranslatorInterface;
use LessHttp\Middleware\Validation\ValidationMiddleware;
use LessValidator\Builder\TypeDocumentValidatorBuilder;
use LessValidator\ValidateResult\ValidateResult;
use LessValidator\Validator;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * @covers \LessHttp\Middleware\Validation\ValidationMiddleware
 */
final class ValidationMiddlewareTest extends TestCase
{
    public function testCached(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $uri = $this->createMock(UriInterface::class);
        $uri
            ->method('getPath')
            ->willReturn('/foo/bar');

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getUri')
            ->willReturn($uri);

        $request
            ->method('getMethod')
            ->willReturn('POST');

        $request
            ->method('getParsedBody')
            ->willReturn([]);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $result = $this->createMock(ValidateResult::class);
        $result
            ->method('isValid')
            ->willReturn(true);

        $validator = $this->createMock(Validator::class);
        $validator
            ->expects(self::once())
            ->method('validate')
            ->with([])
            ->willReturn($result);

        $validatorBuilder = $this->createMock(TypeDocumentValidatorBuilder::class);

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);

        $streamFactory = $this->createMock(StreamFactoryInterface::class);

        $routeInputDocumentor = $this->createMock(RouteInputDocumentor::class);

        $container = $this->createMock(ContainerInterface::class);

        $translator = $this->createMock(TranslatorInterface::class);

        $logger = $this->createMock(LoggerInterface::class);

        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects(self::once())
            ->method('get')
            ->with(md5('validator:POST:/foo/bar'))
            ->willReturn($validator);

        $routes = [];

        $middleware = new ValidationMiddleware(
            $validatorBuilder,
            $routeInputDocumentor,
            $responseFactory,
            $streamFactory,
            $translator,
            $container,
            $logger,
            $cache,
            $routes,
        );

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testInvalid(): void
    {
        $stream = $this->createMock(StreamInterface::class);

        $response = $this->createMock(ResponseInterface::class);
        $response
            ->expects(self::once())
            ->method('withBody')
            ->with($stream)
            ->willReturn($response);

        $response
            ->expects(self::once())
            ->method('withHeader')
            ->with('content-type', 'application/json')
            ->willReturn($response);

        $uri = $this->createMock(UriInterface::class);
        $uri
            ->method('getPath')
            ->willReturn('/foo/bar');

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getUri')
            ->willReturn($uri);

        $request
            ->method('getMethod')
            ->willReturn('POST');

        $request
            ->method('getParsedBody')
            ->willReturn([]);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects(self::never())
            ->method('handle');

        $result = new ErrorValidateResult(
            'fiz',
            ['foo' => 'biz'],
        );

        $validator = $this->createMock(Validator::class);
        $validator
            ->expects(self::once())
            ->method('validate')
            ->with([])
            ->willReturn($result);

        $validatorBuilder = $this->createMock(TypeDocumentValidatorBuilder::class);

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $responseFactory
            ->method('createResponse')
            ->with(422)
            ->willReturn($response);

        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $streamFactory
            ->expects(self::once())
            ->method('createStream')
            ->with(
                json_encode(
                    [
                        'message' => 'Invalid parameters provided',
                        'code' => 'invalidBody',
                        'data' => [
                            'context' => ['foo' => 'biz'],
                            'code' => 'fiz',
                            'message' => 'bar',
                        ],
                    ],
                    flags: JSON_THROW_ON_ERROR,
                )
            )
            ->willReturn($stream);

        $routeInputDocumentor = $this->createMock(RouteInputDocumentor::class);

        $container = $this->createMock(ContainerInterface::class);

        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects(self::once())
            ->method('get')
            ->with(md5('validator:POST:/foo/bar'))
            ->willReturn($validator);

        $routes = [];

        $translator = $this->createMock(TranslatorInterface::class);
        $translator
            ->expects(self::once())
            ->method('trans')
            ->with('validation.fiz', ['%foo%' => 'biz'], null, 'lor')
            ->willReturn('bar');

        $translator
            ->method('getLocale')
            ->willReturn('lor');

        $logger = $this->createMock(LoggerInterface::class);

        $middleware = new ValidationMiddleware(
            $validatorBuilder,
            $routeInputDocumentor,
            $responseFactory,
            $streamFactory,
            $translator,
            $container,
            $logger,
            $cache,
            $routes,
        );

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testNoRouteSettings(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $uri = $this->createMock(UriInterface::class);
        $uri
            ->method('getPath')
            ->willReturn('/foo/bar');

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getUri')
            ->willReturn($uri);

        $request
            ->method('getMethod')
            ->willReturn('POST');

        $request
            ->method('getParsedBody')
            ->willReturn([]);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $validatorBuilder = $this->createMock(TypeDocumentValidatorBuilder::class);

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);

        $streamFactory = $this->createMock(StreamFactoryInterface::class);

        $routeInputDocumentor = $this->createMock(RouteInputDocumentor::class);

        $container = $this->createMock(ContainerInterface::class);

        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects(self::once())
            ->method('get')
            ->with(md5('validator:POST:/foo/bar'))
            ->willReturn(null);

        $routes = [];

        $translator = $this->createMock(TranslatorInterface::class);

        $logger = $this->createMock(LoggerInterface::class);

        $middleware = new ValidationMiddleware(
            $validatorBuilder,
            $routeInputDocumentor,
            $responseFactory,
            $streamFactory,
            $translator,
            $container,
            $logger,
            $cache,
            $routes,
        );

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testDirectValidator(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $uri = $this->createMock(UriInterface::class);
        $uri
            ->method('getPath')
            ->willReturn('/foo/bar');

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getUri')
            ->willReturn($uri);

        $request
            ->method('getMethod')
            ->willReturn('POST');

        $request
            ->method('getParsedBody')
            ->willReturn([]);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $result = $this->createMock(ValidateResult::class);
        $result
            ->method('isValid')
            ->willReturn(true);

        $validator = $this->createMock(Validator::class);
        $validator
            ->expects(self::once())
            ->method('validate')
            ->with([])
            ->willReturn($result);

        $validatorBuilder = $this->createMock(TypeDocumentValidatorBuilder::class);

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);

        $streamFactory = $this->createMock(StreamFactoryInterface::class);

        $routeInputDocumentor = $this->createMock(RouteInputDocumentor::class);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects(self::once())
            ->method('get')
            ->with('fizbiz')
            ->willReturn($validator);

        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects(self::once())
            ->method('get')
            ->with(md5('validator:POST:/foo/bar'))
            ->willReturn(null);

        $cache
            ->expects(self::once())
            ->method('set')
            ->with(md5('validator:POST:/foo/bar'), $validator);

        $routes = [
            'POST:/foo/bar' => [
                'validator' => 'fizbiz',
            ],
        ];

        $translator = $this->createMock(TranslatorInterface::class);

        $logger = $this->createMock(LoggerInterface::class);

        $middleware = new ValidationMiddleware(
            $validatorBuilder,
            $routeInputDocumentor,
            $responseFactory,
            $streamFactory,
            $translator,
            $container,
            $logger,
            $cache,
            $routes,
        );

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testRouteBuildValidator(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $uri = $this->createMock(UriInterface::class);
        $uri
            ->method('getPath')
            ->willReturn('/foo/bar');

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->method('getUri')
            ->willReturn($uri);

        $request
            ->method('getMethod')
            ->willReturn('POST');

        $request
            ->method('getParsedBody')
            ->willReturn(['fiz' => 'biz']);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $result = $this->createMock(ValidateResult::class);
        $result
            ->method('isValid')
            ->willReturn(true);

        $validator = $this->createMock(Validator::class);
        $validator
            ->expects(self::once())
            ->method('validate')
            ->with(['fiz' => 'biz'])
            ->willReturn($result);

        $typeDocument = $this->createMock(TypeDocument::class);

        $validatorBuilder = $this->createMock(TypeDocumentValidatorBuilder::class);
        $validatorBuilder
            ->expects(self::once())
            ->method('fromTypeDocument')
            ->willReturn($validator);

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);

        $streamFactory = $this->createMock(StreamFactoryInterface::class);

        $routeInputDocumentor = $this->createMock(RouteInputDocumentor::class);
        $routeInputDocumentor
            ->expects(self::once())
            ->method('document')
            ->with([])
            ->willReturn($typeDocument);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects(self::never())
            ->method('get');

        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects(self::once())
            ->method('get')
            ->with(md5('validator:POST:/foo/bar'))
            ->willReturn(null);

        $cache
            ->expects(self::once())
            ->method('set')
            ->with(
                md5('validator:POST:/foo/bar'),
                $validator,
            );

        $routes = [
            'POST:/foo/bar' => [
            ],
        ];

        $translator = $this->createMock(TranslatorInterface::class);

        $logger = $this->createMock(LoggerInterface::class);

        $middleware = new ValidationMiddleware(
            $validatorBuilder,
            $routeInputDocumentor,
            $responseFactory,
            $streamFactory,
            $translator,
            $container,
            $logger,
            $cache,
            $routes,
        );

        self::assertSame($response, $middleware->process($request, $handler));
    }
}
