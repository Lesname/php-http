<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Authorization\Constraint\Producer;

use LessHttp\Middleware\Authorization\Constraint\AbstractTypeAllowed;

abstract class AbstractProducerConstraint extends AbstractTypeAllowed
{
    protected function getAllowedType(): string
    {
        return 'identity.producer';
    }
}
