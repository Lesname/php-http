<?php
declare(strict_types=1);

namespace LesHttp\Middleware\Condition;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ConditionMiddlewareFactory
{
    public const string ROUTE_KEY = 'conditions';

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ConditionMiddleware
    {
        $responseFactory = $container->get(ResponseFactoryInterface::class);
        assert($responseFactory instanceof ResponseFactoryInterface);

        $streamFactory = $container->get(StreamFactoryInterface::class);
        assert($streamFactory instanceof StreamFactoryInterface);

        $transltor = $container->get(TranslatorInterface::class);
        assert($transltor instanceof TranslatorInterface);

        $config = $container->get('config');
        assert(is_array($config));

        assert(is_array($config['routes']));

        return new ConditionMiddleware(
            $responseFactory,
            $streamFactory,
            $transltor,
            $this->parseConditions($config['routes']),
            $container,
        );
    }

    /**
     * @param array<mixed> $routes
     *
     * @return array<string, array<string>>
     *
     * @psalm-suppress MixedReturnTypeCoercion
     */
    private function parseConditions(array $routes): array
    {
        $prerequisites = [];

        foreach ($routes as $key => $route) {
            assert(is_array($route));

            if (isset($route[self::ROUTE_KEY]) && is_array($route[self::ROUTE_KEY])) {
                $prerequisites[$key] = $route[self::ROUTE_KEY];
            }
        }

        // @phpstan-ignore return.type
        return $prerequisites;
    }
}
