<?php

declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Authorization\Constraint\Chain;

use LesHttp\Middleware\AccessControl\Authorization\Constraint\AuthorizationConstraint;

/**
 * @psalm-immutable
 */
final class AuthorizationConstraintChain
{
    /**
     * @param non-empty-list<AuthorizationConstraintChain|AuthorizationConstraint|class-string<AuthorizationConstraint>> $constraints
     *
     * @psalm-pure
     */
    public function __construct(
        public readonly ChainOperator $operator,
        public readonly array $constraints,
    ) {}

    /**
     * @param array{operator: ChainOperator, constraints: non-empty-list<AuthorizationConstraintChain|AuthorizationConstraint|class-string<AuthorizationConstraint>>} $an_array
     *
     * @psalm-pure
     */
    public static function __set_state(array $an_array): object
    {
        return new self($an_array['operator'], $an_array['constraints']);
    }

    /**
     * @param non-empty-list<AuthorizationConstraintChain|AuthorizationConstraint|class-string<AuthorizationConstraint>> $constraints
     *
     * @psalm-pure
     */
    public static function and(array $constraints): self
    {
        return new self(ChainOperator::And, $constraints);
    }

    /**
     * @param non-empty-list<AuthorizationConstraintChain|AuthorizationConstraint|class-string<AuthorizationConstraint>> $constraints
     *
     * @psalm-pure
     */
    public static function or(array $constraints): self
    {
        return new self(ChainOperator::Or, $constraints);
    }
}
