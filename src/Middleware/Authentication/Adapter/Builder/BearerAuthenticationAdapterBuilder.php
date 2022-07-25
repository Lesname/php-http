<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Authentication\Adapter\Builder;

use LessHttp\Middleware\Authentication\Adapter\AuthenticationAdapter;
use LessHttp\Middleware\Authentication\Adapter\BearerAuthenticationAdapter;
use LessToken\Codec\TokenCodecHelper;

final class BearerAuthenticationAdapterBuilder implements AuthenticationAdapterBuilder
{
    /**
     * @param array<mixed> $config
     */
    public function build(array $config): AuthenticationAdapter
    {
        assert(is_array($config['codec']));

        return new BearerAuthenticationAdapter(
            TokenCodecHelper::fromConfig($config['codec']),
        );
    }
}
