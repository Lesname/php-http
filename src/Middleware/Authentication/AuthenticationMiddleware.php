<?php
declare(strict_types=1);

namespace LesHttp\Middleware\Authentication;

use Override;
use LesHttp\Middleware\Authentication\Adapter\AuthenticationAdapter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @deprecated moved into AccessControl namespace
 */
final class AuthenticationMiddleware implements MiddlewareInterface
{
    /**
     * @param array<AuthenticationAdapter> $adapters
     */
    public function __construct(private readonly array $adapters)
    {}

    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        foreach ($this->adapters as $adapter) {
            $result = $adapter->resolve($request);

            if ($result) {
                $request = $request->withAttribute('identity', $result);

                break;
            }
        }

        return $handler->handle($request);
    }
}
