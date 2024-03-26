<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Condition;

use Closure;
use JsonException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use LessHttp\Middleware\Condition\Constraint\ConditionConstraint;
use LessHttp\Middleware\Condition\Constraint\Result\ConditionConstraintResult;
use LessHttp\Middleware\Condition\Constraint\Result\UnsatisfiedConditionConstraintResult;

final class ConditionMiddleware implements MiddlewareInterface
{
    public const ROUTE_OPTIONS_KEY = 'conditions';

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
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $options = $request->getAttribute('routeOptions');

        if ($options === null) {
            return $handler->handle($request);
        }

        assert(is_array($options));

        foreach ($this->getConditionsForRoute($options) as $condition) {
            $result = $condition->satisfies($request);

            if (!$result->isSatisfied()) {
                $locale = $this->getUseLocale($request);

                $json = json_encode(
                    [
                        'message' => "Condition not met for request",
                        'code' => 'conditionNotSatisfied',
                        'data' => $this->translate($result, $locale),
                    ],
                    JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES,
                );

                return $this
                    ->responseFactory
                    ->createResponse(409)
                    ->withHeader('content-type', 'application/json')
                    ->withBody($this->streamFactory->createStream($json));
            }
        }

        return $handler->handle($request);
    }

    /**
     * @param array<mixed> $options
     *
     * @return iterable<ConditionConstraint>
     */
    private function getConditionsForRoute(array $options): iterable
    {
        foreach ($options[self::ROUTE_OPTIONS_KEY] ?? [] as $condition) {
            assert(is_string($condition));

            yield ($this->conditionContainer)($condition);
        }
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
