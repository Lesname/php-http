<?php

declare(strict_types=1);

namespace LesHttp\Middleware\Route\Handler;

use LesHttp\Router\Route\Route;
use Psr\Http\Message\ServerRequestInterface;
use LesHttp\Middleware\Route\Handler\Response\HandleResponse;

interface RouteHandler
{
    public function handle(ServerRequestInterface $request, Route $route): HandleResponse;
}
