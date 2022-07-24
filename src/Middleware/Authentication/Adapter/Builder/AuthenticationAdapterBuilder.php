<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Authentication\Adapter\Builder;

use LessHttp\Middleware\Authentication\Adapter\AuthenticationAdapter;

interface AuthenticationAdapterBuilder
{
    /**
     * @param array<mixed> $config
     */
    public function build(array $config): AuthenticationAdapter;
}
