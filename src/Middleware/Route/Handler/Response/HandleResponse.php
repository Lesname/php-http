<?php

declare(strict_types=1);

namespace LesHttp\Middleware\Route\Handler\Response;

/**
 * @psalm-immutable
 */
final class HandleResponse
{
    /**
     * @param array<string, string> $headers
     *
     * @psalm-pure
     */
    public function __construct(
        public readonly int $code,
        public readonly mixed $body,
        public readonly array $headers = [],
    ) {}

    /**
     * @psalm-pure
     */
    public static function empty(): self
    {
        return new self(204, null);
    }
}
