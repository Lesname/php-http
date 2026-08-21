<?php

declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Condition\Constraint\Result;

use JsonSerializable;

/**
 * @psalm-immutable
 */
interface ConditionConstraintResult extends JsonSerializable
{
    /**
     * @psalm-pure
     */
    public function isSatisfied(): bool;

    /**
     * @psalm-pure
     */
    public function getCategory(): ResultCategory;
}
