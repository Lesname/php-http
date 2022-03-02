<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Prerequisite;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class PrerequisiteMiddlewareFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    public function __invoke(ContainerInterface $container): PrerequisiteMiddleware
    {
        $responseFactory = $container->get(ResponseFactoryInterface::class);
        assert($responseFactory instanceof ResponseFactoryInterface);

        $streamFactory = $container->get(StreamFactoryInterface::class);
        assert($streamFactory instanceof StreamFactoryInterface);

        $config = $container->get('config');
        assert(is_array($config));

        assert(is_array($config['routes']));

        return new PrerequisiteMiddleware(
            $responseFactory,
            $streamFactory,
            $container,
            $this->parsePrerequisites($config['routes']),
        );
    }

    /**
     * @param array<array{path: string, allowed_methods: array<string>, prerequisites: array<string>|null}> $routes
     *
     * @return array<string, array<string>>
     */
    private function parsePrerequisites(array $routes): array
    {
        $prerequisites = [];

        foreach ($routes as $route) {
            if (isset($route['prerequisites']) && $route['prerequisites']) {
                foreach ($route['allowed_methods'] as $method) {
                    $prerequisites["{$method}:{$route['path']}"] = $route['prerequisites'];
                }
            }
        }

        return $prerequisites;
    }
}
