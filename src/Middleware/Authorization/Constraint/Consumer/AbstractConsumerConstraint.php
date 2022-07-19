<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Authorization\Constraint\Consumer;

use LessHttp\Middleware\Authorization\Constraint\AbstractTypeAllowed;

abstract class AbstractConsumerConstraint extends AbstractTypeAllowed
{
    protected function getAllowedType(): string
    {
        return 'identity.consumer';
    }
}
