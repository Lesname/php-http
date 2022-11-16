<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Cors;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CorsMiddleware implements MiddlewareInterface
{
    /**
     * @param ResponseFactoryInterface $responseFactory
     * @param array<string, array{origins: array<string>, methods: array<string>, headers: array<string>, maxAge: int}> $settings
     */
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly array $settings,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        $settings = $this->settings[$path] ?? $this->settings['default'];

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
        }

        if (isset($settings['origin']) && ($settings['origin'] === '*' || $settings['origin'] === $request->getHeaderLine('origin'))) {
            $response = $response->withHeader('access-control-allow-origin', $settings['origin']);
        }

        if (isset($settings['maxAge'])) {
            $response = $response->withHeader('access-control-max-age', (string)$settings['maxAge']);
        }

        return $response;
    }
}
