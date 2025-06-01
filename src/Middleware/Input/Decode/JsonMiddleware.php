<?php
declare(strict_types=1);

namespace LesHttp\Middleware\Input\Decode;

use Override;
use JsonException;
use LesHttp\Response\ErrorResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final class JsonMiddleware implements MiddlewareInterface
{
    private const IGNORE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
    ) {}

    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->isInputJson($request)) {
            try {
                $decoded = json_decode($request->getBody()->getContents(), true, flags: JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                $stream = $this->streamFactory->createStream(
                    json_encode(
                        new ErrorResponse(
                            'Body expected to be in JSON format',
                            'body.malformed',
                            ['format' => 'JSON'],
                        ),
                        flags: JSON_THROW_ON_ERROR,
                    ),
                );

                return $this
                    ->responseFactory
                    ->createResponse(400)
                    ->withHeader('content-type', 'application/json')
                    ->withBody($stream);
            }

            if (!is_array($decoded)) {
                $stream = $this->streamFactory->createStream(
                    json_encode(
                        new ErrorResponse(
                            'Invalid parameters provided, needs to be an json object for base',
                            'body.invalid',
                        ),
                        flags: JSON_THROW_ON_ERROR,
                    ),
                );

                return $this
                    ->responseFactory
                    ->createResponse(429)
                    ->withHeader('content-type', 'application/json')
                    ->withBody($stream);
            }

            $request = $request->withParsedBody($decoded);
        }


        return $handler->handle($request);
    }

    private function isInputJson(ServerRequestInterface $request): bool
    {
        return !in_array(strtoupper($request->getMethod()), self::IGNORE_METHODS, true)
            &&
            preg_match('#^application/(|\S+\+)json($|[ ;])#', $request->getHeaderLine('Content-Type')) === 1;
    }
}
