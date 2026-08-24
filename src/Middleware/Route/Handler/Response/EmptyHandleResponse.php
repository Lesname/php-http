<?php

declare(strict_types=1);

namespace LesHttp\Middleware\Route\Handler\Response;

use Override;

/**
 * @psalm-immutable
 */
final class EmptyHandleResponse extends AbstractHandleResponse
{
    #[Override]
    public readonly int $code;

    #[Override]
    public readonly mixed $body;

    /**
     * @param array<string, string> $headers
     */
    public function __construct(array $headers = [])
    {
        $this->code = 204;
        $this->body = null;

        parent::__construct($headers);
    }
}
