<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Authorization;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class AuthorizationMiddlewareFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @psalm-suppress MixedArgumentTypeCoercion
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
     * @param array<array{path: string, allowed_methods: array<string>, authorizations: array<string>|null}> $routes
     *
     * @return array<string, array<string>>
     */
    private function parseAuthorizations(array $routes): array
    {
        $authorizations = [];

        foreach ($routes as $route) {
            if (isset($route['authorizations']) && $route['authorizations']) {
                foreach ($route['allowed_methods'] as $method) {
                    $authorizations["{$method}:{$route['path']}"] = $route['authorizations'];
                }
            }
        }

        return $authorizations;
    }
}
