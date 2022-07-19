<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Authorization\Constraint\Account;

use LessHttp\Middleware\Authorization\Constraint\AbstractTypeAllowed;

abstract class AbstractAccountConstraint extends AbstractTypeAllowed
{
    protected function getAllowedType(): string
    {
        return 'identity.account';
    }
}
