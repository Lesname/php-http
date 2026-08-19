<?php

declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Authentication\Adapter;

use LesValueObject\Composite\ForeignReference;
use LesValueObject\Composite\DynamicCompositeValueObject;

/**
 * @psalm-immutable
 */
final class Identity
{
    public function __construct(
        public readonly ForeignReference $reference,
        public readonly DynamicCompositeValueObject $context,
    ) {}
}
