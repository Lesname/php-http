<?php

declare(strict_types=1);

namespace LesHttp\Router;

use LesHttp\Router\Route\Route;
use Psr\Http\Message\RequestInterface;

interface Router
{
    public function match(RequestInterface $request): ?Route;
}
