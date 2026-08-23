<?php

declare(strict_types=1);

namespace LesHttp\Middleware\Route\Handler\Response;

final class HandleResponse
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        public readonly int $code,
        public readonly mixed $body,
        public readonly array $headers = [],
    ) {}

    public static function empty(): self
    {
        return new self(204, null);
    }
}
