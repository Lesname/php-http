<?php
declare(strict_types=1);

namespace LesHttp\Middleware\Authorization\Constraint;

use Override;
use Psr\Http\Message\ServerRequestInterface;
use LesValueObject\Composite\ForeignReference;

/**
 * @deprecated moved into AccessControl namespace
 */
abstract class AbstractIdentityAuthorizationConstraint implements AuthorizationConstraint
{
    /**
     * @psalm-suppress MixedAssignment
     */
    #[Override]
    public function isAllowed(ServerRequestInterface $request): bool
    {
        $identity = $request->getAttribute('identity');

        return $identity instanceof ForeignReference
            && $this->isIdentityAllowed($request, $identity);
    }

    abstract protected function isIdentityAllowed(ServerRequestInterface $request, ForeignReference $identity): bool;
}
