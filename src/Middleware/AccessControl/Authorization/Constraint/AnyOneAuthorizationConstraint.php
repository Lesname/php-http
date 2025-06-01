<?php
declare(strict_types=1);

namespace LesHttp\Middleware\Authorization\Constraint;

use Override;
use Psr\Http\Message\ServerRequestInterface;

final class AnyOneAuthorizationConstraint implements AuthorizationConstraint
{
    #[Override]
    public function isAllowed(ServerRequestInterface $request): bool
    {
        return true;
    }
}
