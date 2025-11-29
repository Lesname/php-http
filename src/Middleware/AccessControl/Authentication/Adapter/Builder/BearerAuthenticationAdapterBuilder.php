<?php

declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Authentication\Adapter\Builder;

use Override;
use LesToken\Codec\TokenCodecHelper;
use LesHttp\Middleware\AccessControl\Authentication\Adapter\AuthenticationAdapter;
use LesHttp\Middleware\AccessControl\Authentication\Adapter\BearerAuthenticationAdapter;

final class BearerAuthenticationAdapterBuilder implements AuthenticationAdapterBuilder
{
    /**
     * @param array<mixed> $config
     */
    #[Override]
    public function build(array $config): AuthenticationAdapter
    {
        assert(is_array($config['codec']));

        return new BearerAuthenticationAdapter(
            TokenCodecHelper::fromConfig($config['codec']),
        );
    }
}
