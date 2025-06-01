<?php
declare(strict_types=1);

namespace LesHttp\Middleware\Route;

use Override;
use LesHttp\Router\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RouterMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly Router $router)
    {}

    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $this->router->match($request);

        if ($route) {
            $request = $request->withAttribute('route', $route);
        }

        return $handler->handle($request);
    }
}
