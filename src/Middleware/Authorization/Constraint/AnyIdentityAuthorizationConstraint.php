<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Authorization\Constraint;

use Psr\Http\Message\ServerRequestInterface;
use LessValueObject\Composite\ForeignReference;

final class AnyIdentityAuthorizationConstraint extends AbstractIdentityAuthorizationConstraint
{
    protected function isIdentityAllowed(ServerRequestInterface $request, ForeignReference $identity): bool
    {
        return true;
    }
}
