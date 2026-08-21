<?php

declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Authorization\Constraint;

use Psr\Http\Message\ServerRequestInterface;

/**
 * @psalm-mutable
 */
interface AuthorizationConstraint
{
    /**
     * @psalm-impure
     */
    public function isAllowed(ServerRequestInterface $request): bool;
}
