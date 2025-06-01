<?php
declare(strict_types=1);

namespace LesHttp\Middleware\Authorization\Constraint;

use Psr\Http\Message\ServerRequestInterface;

/**
 * @deprecated moved into AccessControl namespace
 */
interface AuthorizationConstraint
{
    public function isAllowed(ServerRequestInterface $request): bool;
}
