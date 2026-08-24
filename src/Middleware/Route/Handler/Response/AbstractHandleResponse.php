<?php

declare(strict_types=1);

namespace LesHttp\Middleware\Route\Handler\Response;

use Override;

/**
 * @psalm-immutable
 */
abstract class AbstractHandleResponse implements HandleResponse
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        #[Override]
        public readonly array $headers = [],
    ) {}
}
