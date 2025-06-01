<?php
declare(strict_types=1);

namespace LesHttp\Middleware\Condition\Constraint;

use Psr\Http\Message\ServerRequestInterface;
use LesHttp\Middleware\Condition\Constraint\Result\ConditionConstraintResult;

/**
 * @deprecated moved into AccessControl namespace
 */
interface ConditionConstraint
{
    public function satisfies(ServerRequestInterface $request): ConditionConstraintResult;
}
