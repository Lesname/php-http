<?php

declare(strict_types=1);

namespace LesHttp\Middleware\Response;

use Override;
use Throwable;
use JsonException;
use Psr\Log\LoggerInterface;
use LesHttp\Response\ErrorResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final class CatchExceptionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * @throws JsonException
     */
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $e) {
            $this->logger->critical($e->getMessage(), ['exception' => $e]);
        }

        $body = $this
            ->streamFactory
            ->createStream(
                json_encode(
                    new ErrorResponse(
                        'Internal server error',
                        'server.error',
                    ),
                    flags: JSON_THROW_ON_ERROR,
                )
            );

        return $this
            ->responseFactory
            ->createResponse(500)
            ->withHeader('content-type', 'application/json')
            ->withBody($body);
    }
}
