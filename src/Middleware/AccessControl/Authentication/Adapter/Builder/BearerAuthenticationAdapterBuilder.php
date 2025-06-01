<?php
declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Authentication\Adapter\Builder;

use Override;
use LesHttp\Middleware\Authentication\Adapter\AuthenticationAdapter;
use LesHttp\Middleware\Authentication\Adapter\BearerAuthenticationAdapter;
use LesToken\Codec\TokenCodecHelper;

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
