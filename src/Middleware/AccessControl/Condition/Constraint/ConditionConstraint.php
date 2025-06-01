<?php
declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Condition\Constraint;

use Psr\Http\Message\ServerRequestInterface;
use LesHttp\Middleware\Condition\Constraint\Result\ConditionConstraintResult;

interface ConditionConstraint
{
    public function satisfies(ServerRequestInterface $request): ConditionConstraintResult;
}
