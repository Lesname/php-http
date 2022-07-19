<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Authorization\Constraint\Producer;

use LessValueObject\Composite\ForeignReference;
use Psr\Http\Message\ServerRequestInterface;

final class ProducerConstraint extends AbstractProducerConstraint
{
    protected function isIdentityAllowed(ServerRequestInterface $request, ForeignReference $account): bool
    {
        return true;
    }
}
