<?php

declare(strict_types=1);

namespace LesHttp\Middleware\Route\Handler\Response;

/**
 * @psalm-immutable
 */
interface HandleResponse
{
    public int $code {get ;}
    public mixed $body {get ;}
    /** @var array<string, string> */
    public array $headers {get ;}
}
