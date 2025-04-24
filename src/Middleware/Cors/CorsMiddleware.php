<?php
declare(strict_types=1);

namespace LesHttp\Middleware\Cors;

use Override;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CorsMiddleware implements MiddlewareInterface
{
    /**
     * @param array<array{origins?: array<string>, origin?: string, methods: array<string>, headers: array<string>, maxAge?: int}> $pathSettings
     * @param array{origins?: array<string>, origin?: string, methods: array<string>, headers: array<string>, maxAge?: int} $defaultSettings
     */
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly array $pathSettings,
        private readonly array $defaultSettings,
    ) {}

    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        $settings = $this->pathSettings[$path] ?? $this->defaultSettings;

        if (strtolower($request->getMethod()) === 'options') {
            $response = $this->responseFactory->createResponse(204);

            if ($request->getHeaderLine('access-control-request-method')) {
                $response = $response->withHeader(
                    'access-control-allow-methods',
                    implode(',', $settings['methods']),
                );
            }

            if ($request->getHeaderLine('access-control-request-headers')) {
                $response = $response->withHeader(
                    'access-control-allow-headers',
                    implode(',', $settings['headers']),
                );
            }
        } else {
            $response = $handler->handle($request);
        }

        if (isset($settings['origins']) && in_array($request->getHeaderLine('origin'), $settings['origins'])) {
            $response = $response->withHeader('access-control-allow-origin', $request->getHeaderLine('origin'));
        } elseif (isset($settings['origin']) && ($settings['origin'] === '*' || $settings['origin'] === $request->getHeaderLine('origin'))) {
            $response = $response->withHeader('access-control-allow-origin', $settings['origin']);
        }

        if (isset($settings['maxAge'])) {
            $response = $response->withHeader('access-control-max-age', (string)$settings['maxAge']);
        }

        return $response;
    }
}
