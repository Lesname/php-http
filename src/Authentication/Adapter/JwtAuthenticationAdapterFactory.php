<?php
declare(strict_types=1);

namespace LessHttp\Authentication\Adapter;

use Psr\Container\ContainerInterface;

final class JwtAuthenticationAdapterFactory
{
    public function __invoke(ContainerInterface $container): JwtAuthenticationAdapter
    {
        $config = $container->get('config');
        assert(is_array($config));

        assert(is_array($config['jwt']));
        assert(is_array($config['jwt']['keys']));

        return new JwtAuthenticationAdapter($config['jwt']['keys']);
    }
}
