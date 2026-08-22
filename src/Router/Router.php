<?php

declare(strict_types=1);

namespace LesHttp\Router;

use LesHttp\Router\Route\Route;
use Psr\Http\Message\RequestInterface;

/**
 * @psalm-mutable
 */
interface Router
{
    /**
     * @psalm-impure
     */
    public function match(RequestInterface $request): ?Route;
}
