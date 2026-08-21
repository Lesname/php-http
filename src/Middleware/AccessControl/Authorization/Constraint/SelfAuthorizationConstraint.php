<?php

declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Authorization\Constraint;

use Override;
use Psr\Http\Message\ServerRequestInterface;
use LesValueObject\Composite\ForeignReference;

final class SelfAuthorizationConstraint implements AuthorizationConstraint
{
    #[Override]
    public function isAllowed(ServerRequestInterface $request): bool
    {
        $identityReference = $request->getAttribute('identity.reference');

        if (!$identityReference instanceof ForeignReference) {
            return false;
        }

        $body = $request->getParsedBody();

        if (!is_array($body) || !isset($body['id']) || !is_string($body['id'])) {
            return false;
        }

        return $identityReference->id->isEqual($body['id']);
    }
}
