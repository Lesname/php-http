<?php

declare(strict_types=1);

namespace LesHttp\Middleware\Route\Handler\Response;

use Override;

/**
 * @psalm-immutable
 */
final class ServerErrorHandleResponse extends AbstractHandleResponse
{
    #[Override]
    public readonly int $code;

    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        #[Override]
        public readonly mixed $body,
        array $headers = []
    ) {
        $this->code = 500;

        parent::__construct($headers);
    }
}
