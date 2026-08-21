<?php

declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Authentication\Adapter;

use Psr\Http\Message\ServerRequestInterface;

/**
 * @psalm-mutable
 */
interface AuthenticationAdapter
{
    /**
     * @psalm-impure
     */
    public function resolve(ServerRequestInterface $request): ?Identity;
}
