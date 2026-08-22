<?php

declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Condition\Constraint\Result;

use Override;

/**
 * @psalm-immutable
 */
final class SatisfiedConditionConstraintResult implements ConditionConstraintResult
{
    /**
     * @psalm-pure
     */
    #[Override]
    public function isSatisfied(): bool
    {
        return true;
    }

    /**
     * @psalm-pure
     */
    #[Override]
    public function getCategory(): ResultCategory
    {
        return ResultCategory::Ok;
    }

    /**
     * @psalm-pure
     */
    #[Override]
    public function jsonSerialize(): mixed
    {
        return null;
    }
}
