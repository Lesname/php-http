<?php
declare(strict_types=1);

namespace LesHttp\Middleware\Condition\Constraint\Result;

use Override;

/**
 * @psalm-immutable
 * @deprecated moved into AccessControl namespace
 */
final class SatisfiedConditionConstraintResult implements ConditionConstraintResult
{
    #[Override]
    public function isSatisfied(): bool
    {
        return true;
    }

    #[Override]
    public function jsonSerialize(): mixed
    {
        return null;
    }
}
