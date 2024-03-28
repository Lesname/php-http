<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Authorization\Constraint;

use Psr\Http\Message\ServerRequestInterface;
use LessValueObject\Composite\ForeignReference;

abstract class AbstractIdentityAuthorizationConstraint implements AuthorizationConstraint
{
    /**
     * @psalm-suppress MixedAssignment
     */
    public function isAllowed(ServerRequestInterface $request): bool
    {
        $identity = $request->getAttribute('identity');

        return $identity instanceof ForeignReference
            && $this->isIdentityAllowed($request, $identity);
    }

    abstract protected function isIdentityAllowed(ServerRequestInterface $request, ForeignReference $identity): bool;
}
