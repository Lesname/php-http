<?php
declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Condition\Constraint\Result;

use Override;

/**
 * @psalm-immutable
 */
final class SatisfiedConditionConstraintResult implements ConditionConstraintResult
{
    #[Override]
    public function isSatisfied(): bool
    {
        return true;
    }

    #[Override]
    public function getCategory(): ResultCategory
    {
        return ResultCategory::Ok;
    }

    #[Override]
    public function jsonSerialize(): mixed
    {
        return null;
    }
}
