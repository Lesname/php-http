<?php
declare(strict_types=1);

namespace LesHttp\Middleware\Condition\Constraint\Result;

use JsonSerializable;

/**
 * @psalm-immutable
 */
interface ConditionConstraintResult extends JsonSerializable
{
    public function isSatisfied(): bool;
}
