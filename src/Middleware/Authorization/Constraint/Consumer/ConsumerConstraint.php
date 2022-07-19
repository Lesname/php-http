<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Authorization\Constraint\Consumer;

use LessValueObject\Composite\ForeignReference;
use Psr\Http\Message\ServerRequestInterface;

final class ConsumerConstraint extends AbstractConsumerConstraint
{
    protected function isIdentityAllowed(ServerRequestInterface $request, ForeignReference $account): bool
    {
        return true;
    }
}
