<?php

declare(strict_types=1);

namespace LesHttp\Middleware\Route\Handler\Response;

use Override;

/**
 * @psalm-immutable
 */
final class ClientErrorHandleResponse extends AbstractHandleResponse
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
        $this->code = 400;

        parent::__construct($headers);
    }
}
