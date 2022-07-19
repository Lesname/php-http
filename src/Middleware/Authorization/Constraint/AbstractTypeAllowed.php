<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Authorization\Constraint;

use LessValueObject\Composite\ForeignReference;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractTypeAllowed implements AuthorizationConstraint
{
    public function isAllowed(ServerRequestInterface $request): bool
    {
        $identity = $request->getAttribute('identity');
        assert($identity instanceof ForeignReference || $identity === null);

        return $identity instanceof ForeignReference
            && $identity->type->getValue() === $this->getAllowedType()
            && $this->isIdentityAllowed($request, $identity);
    }

    abstract protected function getAllowedType(): string;

    abstract protected function isIdentityAllowed(ServerRequestInterface $request, ForeignReference $account): bool;
}
