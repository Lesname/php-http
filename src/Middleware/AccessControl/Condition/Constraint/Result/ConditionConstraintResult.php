<?php

declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Condition\Constraint\Result;

use JsonSerializable;

/**
 * @psalm-immutable
 */
interface ConditionConstraintResult extends JsonSerializable
{
    public function isSatisfied(): bool;

    public function getCategory(): ResultCategory;
}
