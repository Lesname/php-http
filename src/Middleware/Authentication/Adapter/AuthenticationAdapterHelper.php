<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Authentication\Adapter;

use LessHttp\Middleware\Authentication\Adapter\Builder\AuthenticationAdapterBuilder;

final class AuthenticationAdapterHelper
{
    public static function fromConfig(array $config): AuthenticationAdapter
    {
        assert(is_string($config['builder']));
        assert(is_subclass_of($config['builder'], AuthenticationAdapterBuilder::class));

        $builder = new $config['builder']();

        assert(is_array($config['config']));

        return $builder->build($config['config']);
    }
}
