<?php
declare(strict_types=1);

namespace LessHttp\Response;

use LessValueObject\Composite\AbstractCompositeValueObject;

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
