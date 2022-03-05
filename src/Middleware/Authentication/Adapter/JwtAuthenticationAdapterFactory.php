<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Authentication\Adapter;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

final class JwtAuthenticationAdapterFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): JwtAuthenticationAdapter
    {
        $config = $container->get('config');
        assert(is_array($config));

        assert(is_array($config['jwt']));
        assert(is_array($config['jwt']['keys']));

        $keys = $config['jwt']['keys'];
        /** @var array<array{keyMaterial: string, algorithm: string}> $keys */

        return new JwtAuthenticationAdapter($keys);
    }
}
