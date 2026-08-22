<?php

declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Authorization\Constraint;

use Override;
use Psr\Http\Message\ServerRequestInterface;
use LesValueObject\Composite\ForeignReference;

/**
 * @psalm-mutable
 */
final class AnyIdentityAuthorizationConstraint extends AbstractIdentityAuthorizationConstraint
{
    /**
     * @psalm-pure
     */
    #[Override]
    protected function isIdentityAllowed(ServerRequestInterface $request, ForeignReference $identity): bool
    {
        return true;
    }
}
