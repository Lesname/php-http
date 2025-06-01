<?php
declare(strict_types=1);

namespace LesHttp\Middleware\Authentication\Adapter\Builder;

use LesHttp\Middleware\Authentication\Adapter\AuthenticationAdapter;

/**
 * @deprecated moved into AccessControl namespace
 */
interface AuthenticationAdapterBuilder
{
    /**
     * @param array<mixed> $config
     */
    public function build(array $config): AuthenticationAdapter;
}
