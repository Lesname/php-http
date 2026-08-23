<?php

declare(strict_types=1);

namespace LesHttp\Middleware\Route;

use Override;
use JsonException;
use RuntimeException;
use LesHttp\Router\Route\Route;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use LesHttp\Middleware\Exception\NoRouteSet;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use LesHttp\Router\Route\Exception\OptionNotSet;
use LesHttp\Middleware\Route\Exception\NoHandler;
use LesHttp\Middleware\Route\Handler\RouteHandler;

final class DispatchMiddleware implements MiddlewareInterface
{
    /**
     * @psalm-pure
     */
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly ContainerInterface $container,
    ) {}

    /**
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NoHandler
     * @throws NoRouteSet
     * @throws NotFoundExceptionInterface
     * @throws OptionNotSet
     */
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $request->getAttribute('route');

        if (!$route instanceof Route) {
            throw new NoRouteSet();
        }

        if ($route->hasOption('handler')) {
            $option = $route->getOption('handler');
        } elseif ($route->hasOption('middleware')) {
            $option = $route->getOption('middleware');
        } else {
            throw new NoHandler($request);
        }

        if (!is_string($option)) {
            throw new RuntimeException();
        }

        $handler = $this->container->get($option);

        if ($handler instanceof RequestHandlerInterface) {
            return $handler->handle($request);
        }

        if ($handler instanceof RouteHandler) {
            return $this->handle($request, $route, $handler);
        }

        throw new RuntimeException(get_debug_type($handler) . ' is not a valid handler');
    }

    /**
     * @throws JsonException
     */
    private function handle(ServerRequestInterface $request, Route $route, RouteHandler $handler): ResponseInterface
    {
        $handleResponse = $handler->handle($request, $route);

        $response = $this->responseFactory->createResponse($handleResponse->code);

        if ($handleResponse->code !== 204) {
            $body = $this->streamFactory->createStream(
                json_encode(
                    $handleResponse->body,
                    flags: JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES,
                ),
            );

            $response = $response
                ->withHeader('content-type', 'application/json')
                ->withBody($body);
        }

        foreach ($handleResponse->headers as $name => $value) {
            $response = $response->withAddedHeader($name, $value);
        }

        return $response;
    }
}
