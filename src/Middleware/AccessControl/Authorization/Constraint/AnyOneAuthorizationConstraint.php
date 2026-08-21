<?php

declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Authorization\Constraint;

use Override;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @psalm-mutable
 */
final class AnyOneAuthorizationConstraint implements AuthorizationConstraint
{
    /**
     * @psalm-pure
     */
    #[Override]
    public function isAllowed(ServerRequestInterface $request): bool
    {
        return true;
    }
}
