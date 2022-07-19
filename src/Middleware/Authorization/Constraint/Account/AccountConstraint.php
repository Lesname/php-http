<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Authorization\Constraint\Account;

use LessValueObject\Composite\ForeignReference;
use Psr\Http\Message\ServerRequestInterface;

final class AccountConstraint extends AbstractAccountConstraint
{
    protected function isIdentityAllowed(ServerRequestInterface $request, ForeignReference $account): bool
    {
        return true;
    }
}
