<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Condition\Constraint;

use Psr\Http\Message\ServerRequestInterface;
use LessHttp\Middleware\Condition\Constraint\Result\ConditionConstraintResult;

interface ConditionConstraint
{
    public function satisfies(ServerRequestInterface $request): ConditionConstraintResult;
}
