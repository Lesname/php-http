<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Validation;

use JsonException;
use Psr\Log\LoggerInterface;
use LessValidator\ValidateResult;
use LessDocumentor\Route\Input\RouteInputDocumentor;
use LessHttp\Response\ErrorResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use LessValidator\Builder\TypeDocumentValidatorBuilder;
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
     * @param TypeDocumentValidatorBuilder $typeDocumentValidatorBuilder
     * @param RouteInputDocumentor $routeInputDocumentor
     * @param ResponseFactoryInterface $responseFactory
     * @param StreamFactoryInterface $streamFactory
     * @param ContainerInterface $container
     * @param CacheInterface $cache
     * @param array<string, array<mixed>> $routes
     */
    public function __construct(
        private readonly TypeDocumentValidatorBuilder $typeDocumentValidatorBuilder,
        private readonly RouteInputDocumentor $routeInputDocumentor,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly TranslatorInterface $translator,
        private readonly ContainerInterface $container,
        private readonly LoggerInterface $logger,
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
                $locale = $this->getPreferredLanguage($request);

                $stream = $this->streamFactory->createStream(
                    json_encode(
                        new ErrorResponse(
                            'Invalid parameters provided',
                            'invalidBody',
                            $this->toData($result, $locale),
                        ),
                        flags: JSON_THROW_ON_ERROR,
                    ),
                );

                return $this
                    ->responseFactory
                    ->createResponse(422)
                    ->withHeader('content-type', 'application/json')
                    ->withBody($stream);
            }
        }

        return $handler->handle($request);
    }

    /**
     * @psalm-suppress MixedAssignment
     */
    private function getPreferredLanguage(ServerRequestInterface $request): string
    {
        $useLocale = $request->getAttribute('useLocale');

        return is_string($useLocale)
            ? $useLocale
            : $this->translator->getLocale();
    }

    private function toData(ValidateResult\ValidateResult $result, string $locale): mixed
    {
        if (
            $result instanceof ValidateResult\Collection\SelfValidateResult
            ||
            $result instanceof ValidateResult\Composite\SelfValidateResult
        ) {
            return ['self' => $this->toData($result->self, $locale)];
        }

        if ($result instanceof ValidateResult\Collection\ItemsValidateResult) {
            return [
                'items' => array_map(
                    fn (ValidateResult\ValidateResult $item): mixed => $this->toData($item, $locale),
                    $result->items,
                ),
            ];
        }

        if ($result instanceof ValidateResult\Composite\PropertiesValidateResult) {
            return [
                'properties' => array_map(
                    fn (ValidateResult\ValidateResult $item): mixed => $this->toData($item, $locale),
                    $result->properties,
                ),
            ];
        }

        if ($result instanceof ValidateResult\ErrorValidateResult) {
            $context = [];

            foreach ($result->context as $key => $value) {
                $context["%{$key}%"] = is_array($value)
                    ? implode(', ', $value)
                    : $value;
            }

            $message = $this->translator->trans($result->code, $context, locale: $locale);

            if ($message === $result->code) {
                $this->logger->info("Missing translation for '{$message}' with locale '{$locale}'");
            }

            return [
                'context' => $result->context,
                'code' => $result->code,
                'message' => $message,
            ];
        }

        return $result;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidArgumentException
     *
     * @psalm-suppress MixedAssignment
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

        $document = $this->routeInputDocumentor->document($routeSettings);

        return $this
            ->typeDocumentValidatorBuilder
            ->fromTypeDocument($document);
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
