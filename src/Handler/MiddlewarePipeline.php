<?php

declare(strict_types=1);

namespace LesHttp\Handler;

use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use LesHttp\Handler\Exception\NoHandlers;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

final class MiddlewarePipeline implements RequestHandlerInterface
{
    /**
     * @param array<MiddlewareInterface> $handlers
     */
    public function __construct(private readonly array $handlers)
    {}

    /**
     * @throws NoHandlers
     */
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (count($this->handlers) === 0) {
            throw new NoHandlers();
        }

        $handlers = $this->handlers;
        $handler = array_shift($handlers);

        return $handler->process($request, new self($handlers));
    }
}
