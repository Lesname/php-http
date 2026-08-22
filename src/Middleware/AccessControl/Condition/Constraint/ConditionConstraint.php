<?php

declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Condition\Constraint;

use Psr\Http\Message\ServerRequestInterface;
use LesHttp\Middleware\AccessControl\Condition\Constraint\Result\ConditionConstraintResult;

/**
 * @psalm-mutable
 */
interface ConditionConstraint
{
    /**
     * @psalm-impure
     */
    public function satisfies(ServerRequestInterface $request): ConditionConstraintResult;
}
