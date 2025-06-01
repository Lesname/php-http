<?php
declare(strict_types=1);

namespace LesHttp\Middleware\Route;

use Override;
use RuntimeException;
use LesHttp\Router\Route\Route;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use LesHttp\Middleware\Exception\NoRouteSet;

final class DispatchMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly ContainerInterface $container)
    {}

    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $request->getAttribute('route');

        if (!$route instanceof Route) {
            throw new NoRouteSet();
        }

        $middleware = $route->getOption('middleware');
        assert(is_string($middleware));

        $middleware = $this->container->get($middleware);

        if (!$middleware instanceof RequestHandlerInterface) {
            throw new RuntimeException();
        }

        return $middleware->handle($request);
    }
}
