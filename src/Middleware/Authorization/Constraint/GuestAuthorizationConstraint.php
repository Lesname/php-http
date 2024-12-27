<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Authorization\Constraint;

use Psr\Http\Message\ServerRequestInterface;

abstract class GuestAuthorizationConstraint implements AuthorizationConstraint
{
    public function isAllowed(ServerRequestInterface $request): bool
    {
        return $request->getAttribute('identity') === null;
    }
}
