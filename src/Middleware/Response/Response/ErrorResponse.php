<?php

declare(strict_types=1);

namespace LesHttp\Middleware\Response\Response;

use LesValueObject\Composite\AbstractCompositeValueObject;

/**
 * @psalm-immutable
 */
final class ErrorResponse extends AbstractCompositeValueObject
{
    public function __construct(
        public readonly string $message,
        public readonly string $code,
        public readonly mixed $data = null,
    ) {}
}
