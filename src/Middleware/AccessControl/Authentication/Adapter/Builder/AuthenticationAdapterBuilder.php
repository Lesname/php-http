<?php
declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Authentication\Adapter\Builder;

use LesHttp\Middleware\Authentication\Adapter\AuthenticationAdapter;

interface AuthenticationAdapterBuilder
{
    /**
     * @param array<mixed> $config
     */
    public function build(array $config): AuthenticationAdapter;
}
