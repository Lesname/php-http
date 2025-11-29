<?php

declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Condition;

use Closure;
use Override;
use JsonException;
use LesHttp\Router\Route\Route;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use LesHttp\Middleware\Exception\NoRouteSet;
use Psr\Http\Message\ResponseFactoryInterface;
use LesHttp\Router\Route\Exception\OptionNotSet;
use Symfony\Contracts\Translation\TranslatorInterface;
use LesHttp\Middleware\AccessControl\Condition\Constraint\ConditionConstraint;
use LesHttp\Middleware\AccessControl\Condition\Constraint\Result\ConditionConstraintResult;
use LesHttp\Middleware\AccessControl\Condition\Constraint\Result\UnsatisfiedConditionConstraintResult;

final class ConditionMiddleware implements MiddlewareInterface
{
    /** @var Closure(string $key): ConditionConstraint */
    private Closure $conditionContainer;

    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly TranslatorInterface $translator,
        ContainerInterface $container,
    ) {
        $this->conditionContainer = static function (string $key) use ($container): ConditionConstraint {
            $condition = $container->get($key);
            assert($condition instanceof ConditionConstraint);

            return $condition;
        };
    }

    /**
     * @throws JsonException
     * @throws NoRouteSet
     * @throws OptionNotSet
     */
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $request->getAttribute('route');

        if (!$route instanceof Route) {
            throw new NoRouteSet();
        }

        $conditions = $route->getOption('conditions');
        assert(is_array($conditions));

        foreach ($conditions as $conditionKey) {
            assert(is_string($conditionKey));

            $result = ($this->conditionContainer)($conditionKey)->satisfies($request);

            if (!$result->isSatisfied()) {
                $locale = $this->getUseLocale($request);

                $json = json_encode(
                    [
                        'message' => $this->translator->trans('condition.notSatisfied'),
                        'code' => 'conditionNotSatisfied',
                        'data' => $this->translate($result, $locale),
                    ],
                    JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES,
                );

                return $this
                    ->responseFactory
                    ->createResponse($result->getCategory()->getHttpCode())
                    ->withHeader('content-type', 'application/json')
                    ->withBody($this->streamFactory->createStream($json));
            }
        }

        return $handler->handle($request);
    }

    private function translate(ConditionConstraintResult $result, string $locale): mixed
    {
        if ($result instanceof UnsatisfiedConditionConstraintResult) {
            $translatorContext = [];

            foreach ($result->context as $key => $value) {
                $translatorContext["%{$key}%"] = $value;
            }

            return [
                'code' => $result->code,
                'context' => $result->context,
                'message' => $this->translator->trans(
                    "condition.{$result->code}",
                    $translatorContext,
                    locale: $locale,
                ),
            ];
        }

        return $result;
    }

    private function getUseLocale(ServerRequestInterface $request): string
    {
        $useLocale = $request->getAttribute('useLocale');
        assert(is_string($useLocale) || $useLocale === null);

        return is_string($useLocale)
            ? $useLocale
            : $this->translator->getLocale();
    }
}
