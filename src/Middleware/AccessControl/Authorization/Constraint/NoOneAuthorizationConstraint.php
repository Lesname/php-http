<?php

declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Authorization\Constraint;

use Override;
use Psr\Http\Message\ServerRequestInterface;

final class NoOneAuthorizationConstraint implements AuthorizationConstraint
{
    #[Override]
    public function isAllowed(ServerRequestInterface $request): bool
    {
        return false;
    }
}
