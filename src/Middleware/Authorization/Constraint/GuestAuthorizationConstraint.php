<?php
declare(strict_types=1);

namespace LesHttp\Middleware\Authorization\Constraint;

use Override;
use Psr\Http\Message\ServerRequestInterface;

abstract class GuestAuthorizationConstraint implements AuthorizationConstraint
{
    #[Override]
    public function isAllowed(ServerRequestInterface $request): bool
    {
        return $request->getAttribute('identity') === null;
    }
}
