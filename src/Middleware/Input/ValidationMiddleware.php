<?php

declare(strict_types=1);

namespace LesHttp\Middleware\Input;

use Override;
use JsonException;
use NumberFormatter;
use LesValidator\Validator;
use Psr\Log\LoggerInterface;
use LesHttp\Router\Route\Route;
use LesValidator\ValidateResult;
use Psr\SimpleCache\CacheInterface;
use LesHttp\Response\ErrorResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use LesHttp\Middleware\Exception\NoRouteSet;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use LesHttp\Router\Route\Exception\OptionNotSet;
use LesDocumentor\Route\Input\RouteInputDocumentor;
use Symfony\Contracts\Translation\TranslatorInterface;
use LesValidator\Builder\TypeDocumentValidatorBuilder;

final class ValidationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly RouteInputDocumentor $routeInputDocumentor,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly TranslatorInterface $translator,
        private readonly ContainerInterface $container,
        private readonly LoggerInterface $logger,
        private readonly CacheInterface $cache,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     * @throws NotFoundExceptionInterface
     * @throws JsonException
     */
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $validator = $this->getValidatorFromRequest($request);
        $result = $validator->validate($request->getParsedBody());

        if (!$result->isValid()) {
            $locale = $this->getPreferredLanguage($request);

            $stream = $this->streamFactory->createStream(
                json_encode(
                    new ErrorResponse(
                        'Invalid parameters provided',
                        'body.invalid',
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
                    fn(ValidateResult\ValidateResult $item): mixed => $this->toData($item, $locale),
                    $result->items,
                ),
            ];
        }

        if ($result instanceof ValidateResult\Composite\PropertiesValidateResult) {
            return [
                'properties' => array_map(
                    fn(ValidateResult\ValidateResult $item): mixed => $this->toData($item, $locale),
                    $result->properties,
                ),
            ];
        }

        if ($result instanceof ValidateResult\ErrorValidateResult) {
            $context = [];

            $numberFormatter = new NumberFormatter($locale, NumberFormatter::DECIMAL);

            foreach ($result->context as $key => $value) {
                $context["%{$key}%"] = match (true) {
                    is_int($value), is_float($value) => $numberFormatter->format($value),
                    is_array($value) => implode(', ', $value),
                    default => $value,
                };
            }

            $code = "validation.{$result->code}";
            $message = $this->translator->trans($code, $context, locale: $locale);

            if ($message === $code) {
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
     * @throws NoRouteSet
     * @throws NotFoundExceptionInterface
     * @throws OptionNotSet
     * @throws ContainerExceptionInterface
     * @throws InvalidArgumentException
     */
    private function getValidatorFromRequest(ServerRequestInterface $request): Validator
    {
        $routeKey = "{$request->getMethod()}:{$request->getUri()->getPath()}";
        $cacheKey = md5("validator:{$routeKey}");

        $cached = $this->cache->get($cacheKey);

        if ($cached instanceof Validator) {
            return $cached;
        }

        $route = $request->getAttribute('route');

        if (!$route instanceof Route) {
            throw new NoRouteSet();
        }

        $validator = $this->getValidatorFromRoute($route);
        $this->cache->set($cacheKey, $validator);

        return $validator;
    }

    /**
     * @throws OptionNotSet
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getValidatorFromRoute(Route $route): Validator
    {
        if ($route->hasOption('validator')) {
            $optionValidator = $route->getOption('validator');

            assert(is_string($optionValidator));

            $validator = $this->container->get($optionValidator);
            assert($validator instanceof Validator);

            return $validator;
        }

        $document = $this->routeInputDocumentor->document($route->toArray());

        return (new TypeDocumentValidatorBuilder($document))
            ->build();
    }
}
