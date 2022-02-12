<?php
declare(strict_types=1);

namespace LessHttp\Validation;

use JsonException;
use LessDocumentor\Route\RouteDocumentor;
use LessDocumentor\Type\Document\TypeDocument;
use LessValidator\Builder\RouteDocumentValidatorBuilder;
use LessValidator\ChainValidator;
use LessValidator\Composite\PropertyKeysValidator;
use LessValidator\Composite\PropertyValuesValidator;
use LessValidator\Validator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

final class ValidationMiddleware implements MiddlewareInterface
{
    /**
     * @param ResponseFactoryInterface $responseFactory
     * @param StreamFactoryInterface $streamFactory
     * @param RouteDocumentor $routeDocumentor
     * @param ContainerInterface $container
     * @param CacheInterface $cache
     * @param array<string, array<mixed>> $routes
     */
    public function __construct(
        private readonly RouteDocumentValidatorBuilder $validatorBuilder,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly RouteDocumentor $routeDocumentor,
        private readonly ContainerInterface $container,
        private readonly CacheInterface $cache,
        private readonly array $routes,
    ) {}

    /**
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     * @throws NotFoundExceptionInterface
     * @throws JsonException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $validator = $this->getValidatorFromRequest($request);
        $body = $request->getParsedBody();

        if ($validator) {
            $result = $validator->validate($body);

            if (!$result->isValid()) {
                $json = json_encode(
                    [
                        'message' => 'Invalid parameters provided',
                        'code' => 'invalidBody',
                        'data' => $result,
                    ],
                    flags: JSON_THROW_ON_ERROR,
                );

                return $this
                    ->responseFactory
                    ->createResponse(422)
                    ->withHeader('content-type', 'application/json')
                    ->withBody($this->streamFactory->createStream($json));
            }
        }

        return $handler->handle($request);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidArgumentException
     */
    private function getValidatorFromRequest(ServerRequestInterface $request): ?Validator
    {
        $routeKey = "{$request->getMethod()}:{$request->getUri()->getPath()}";
        $cacheKey = md5("validator:{$routeKey}");

        $cached = $this->cache->get($cacheKey);

        if ($cached instanceof Validator) {
            return $cached;
        }

        $routeSettings = $this->getRouteSettings($request);

        if ($routeSettings === null) {
            return null;
        }

        $validator = $this->getValidatorFromRoute($routeSettings);
        $this->cache->set($cacheKey, $validator);

        return $validator;
    }

    /**
     * @param array<mixed> $routeSettings
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getValidatorFromRoute(array $routeSettings): Validator
    {
        if (isset($routeSettings['validator'])) {
            assert(is_string($routeSettings['validator']));

            $validator = $this->container->get($routeSettings['validator']);
            assert($validator instanceof Validator);

            return $validator;
        }

        $routeDocument = $this->routeDocumentor->document($routeSettings);

        return new ChainValidator(
            [
                new PropertyKeysValidator(array_keys($routeDocument->getInput())),
                new PropertyValuesValidator(
                    array_map(
                        fn (TypeDocument $document) => $this
                            ->validatorBuilder
                            ->fromTypeDocument($document),
                        $routeDocument->getInput(),
                    )
                ),
            ],
        );
    }

    /**
     * @return array<mixed>
     */
    private function getRouteSettings(ServerRequestInterface $request): ?array
    {
        $routeKey = "{$request->getMethod()}:{$request->getUri()->getPath()}";

        return $this->routes[$routeKey] ?? null;
    }
}
