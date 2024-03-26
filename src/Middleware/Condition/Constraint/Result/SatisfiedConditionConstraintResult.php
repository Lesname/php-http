<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Condition\Constraint\Result;

/**
 * @psalm-immutable
 */
final class SatisfiedConditionConstraintResult implements ConditionConstraintResult
{
    public function isSatisfied(): bool
    {
        return true;
    }

    public function jsonSerialize(): mixed
    {
        return null;
    }
}
