<?php
declare(strict_types=1);

namespace LesHttp\Middleware\Authorization;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class AuthorizationMiddlewareFactory
{
    public const string ROUTE_KEY = 'authorizations';

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AuthorizationMiddleware
    {
        $responseFactory = $container->get(ResponseFactoryInterface::class);
        assert($responseFactory instanceof ResponseFactoryInterface);

        $streamFactory = $container->get(StreamFactoryInterface::class);
        assert($streamFactory instanceof StreamFactoryInterface);

        $config = $container->get('config');
        assert(is_array($config));

        assert(is_array($config['routes']));

        return new AuthorizationMiddleware(
            $responseFactory,
            $streamFactory,
            $container,
            $this->parseAuthorizations($config['routes']),
        );
    }

    /**
     * @param array<mixed> $routes
     *
     * @return array<string, array<string>>
     *
     * @psalm-suppress MixedInferredReturnType
     * @psalm-suppress MixedReturnTypeCoercion
     * @psalm-suppress MixedReturnStatement
     * @psalm-suppress MixedAssignment
     */
    private function parseAuthorizations(array $routes): array
    {
        $authorizations = [];

        foreach ($routes as $key => $route) {
            assert(is_array($route));
            assert(is_string($key));

            if (isset($route[self::ROUTE_KEY])) {
                $authorizations[$key] = $route[self::ROUTE_KEY];
            }
        }

        // @phpstan-ignore return.type
        return $authorizations;
    }
}
