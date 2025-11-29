<?php

declare(strict_types=1);

namespace LesHttp\Middleware\Route;

use Override;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final class NoRouteMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
    ) {
    }

    /**
     * @throws JsonException
     */
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getAttribute('route') === null) {
            $stream = $this->streamFactory->createStream(
                json_encode(
                    [
                        'message' => 'No route found',
                        'code' => 'route.notFound',
                        'context' => [
                            'path' => $request->getUri()->getPath(),
                            'method' => $request->getMethod(),
                        ],
                    ],
                    flags: JSON_THROW_ON_ERROR,
                ),
            );

            return $this
                ->responseFactory
                ->createResponse(404)
                ->withHeader('content-type', 'application/json')
                ->withBody($stream);
        }

        return $handler->handle($request);
    }
}
