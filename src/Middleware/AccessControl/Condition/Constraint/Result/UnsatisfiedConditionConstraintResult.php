<?php

declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Condition\Constraint\Result;

use Override;

/**
 * @psalm-immutable
 */
final class UnsatisfiedConditionConstraintResult implements ConditionConstraintResult
{
    public readonly ResultCategory $errorType;

    /**
     * @param array<string, string | int | float> $context
     *
     * @internal
     */
    public function __construct(
        public readonly string $code,
        public readonly array $context = [],
        ?ResultCategory $errorType = null,
    ) {
        $this->errorType = $errorType ?? ResultCategory::Conflict;
    }

    /**
     * @param array<string, string | int | float> $context
     */
    public static function conflict(string $code, array $context = []): UnsatisfiedConditionConstraintResult
    {
        return new self($code, $context, ResultCategory::Conflict);
    }

    /**
     * @param array<string, string | int | float> $context
     */
    public static function constraint(string $code, array $context = []): UnsatisfiedConditionConstraintResult
    {
        return new self($code, $context, ResultCategory::Constraint);
    }

    #[Override]
    public function isSatisfied(): bool
    {
        return false;
    }

    #[Override]
    public function getCategory(): ResultCategory
    {
        return $this->errorType;
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
