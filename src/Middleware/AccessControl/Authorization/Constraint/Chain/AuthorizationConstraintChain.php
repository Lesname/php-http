<?php

declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Authorization\Constraint\Chain;

use LesHttp\Middleware\AccessControl\Authorization\Constraint\AuthorizationConstraint;

final class AuthorizationConstraintChain
{
    /**
     * @param non-empty-list<AuthorizationConstraintChain|AuthorizationConstraint|class-string<AuthorizationConstraint>> $constraints
     */
    public function __construct(
        public readonly ChainOperator $operator,
        public readonly array $constraints,
    ) {}

    /**
     * @param array{operator: ChainOperator, constraints: non-empty-list<AuthorizationConstraintChain|AuthorizationConstraint|class-string<AuthorizationConstraint>>} $an_array
     */
    public static function __set_state(array $an_array): object
    {
        return new self($an_array['operator'], $an_array['constraints']);
    }

    /**
     * @param non-empty-list<AuthorizationConstraintChain|AuthorizationConstraint|class-string<AuthorizationConstraint>> $constraints
     */
    public static function and(array $constraints): self
    {
        return new self(ChainOperator::And, $constraints);
    }

    /**
     * @param non-empty-list<AuthorizationConstraintChain|AuthorizationConstraint|class-string<AuthorizationConstraint>> $constraints
     */
    public static function or(array $constraints): self
    {
        return new self(ChainOperator::Or, $constraints);
    }
}
