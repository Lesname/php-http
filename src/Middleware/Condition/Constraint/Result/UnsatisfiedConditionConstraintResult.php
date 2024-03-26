<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Condition\Constraint\Result;

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

    public function isSatisfied(): bool
    {
        return false;
    }

    /**
     * @return array<string, string | int | float>
     */
    public function jsonSerialize(): array
    {
        return [
            'code' => $this->code,
            'context' => $this->context,
        ];
    }
}
