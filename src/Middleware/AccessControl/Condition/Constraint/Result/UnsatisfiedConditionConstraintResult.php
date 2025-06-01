<?php
declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Condition\Constraint\Result;

use Override;

/**
 * @psalm-immutable
 */
final class UnsatisfiedConditionConstraintResult implements ConditionConstraintResult
{
    /**
     * @param array<string, string | int | float> $context
     */
    public function __construct(
        public readonly string $code,
        public readonly array $context = []
    ) {}

    #[Override]
    public function isSatisfied(): bool
    {
        return false;
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'code' => $this->code,
            'context' => $this->context,
        ];
    }
}
