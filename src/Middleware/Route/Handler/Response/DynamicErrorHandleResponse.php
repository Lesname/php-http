<?php

declare(strict_types=1);

namespace LesHttp\Middleware\Route\Handler\Response;

use Override;

/**
 * @psalm-immutable
 */
final class DynamicErrorHandleResponse extends AbstractHandleResponse
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        #[Override]
        public readonly int $code,
        #[Override]
        public readonly mixed $body,
        array $headers = []
    ) {
        parent::__construct($headers);
    }
}
