<?php

declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Authorization\Constraint;

use Override;
use Psr\Http\Message\ServerRequestInterface;
use LesValueObject\Composite\ForeignReference;

abstract class AbstractIdentityAuthorizationConstraint implements AuthorizationConstraint
{
    /**
     * @psalm-suppress MixedAssignment
     *
     * @psalm-impure
     */
    #[Override]
    public function isAllowed(ServerRequestInterface $request): bool
    {
        $identity = $request->getAttribute('identity.reference');

        return $identity instanceof ForeignReference
            && $this->isIdentityAllowed($request, $identity);
    }

    /**
     * @psalm-impure
     */
    abstract protected function isIdentityAllowed(ServerRequestInterface $request, ForeignReference $identity): bool;
}
