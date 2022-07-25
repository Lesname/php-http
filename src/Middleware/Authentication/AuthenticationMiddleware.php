<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Authentication;

use LessHttp\Middleware\Authentication\Adapter\AuthenticationAdapter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AuthenticationMiddleware implements MiddlewareInterface
{
    /**
     * @param array<AuthenticationAdapter> $adapters
     */
    public function __construct(private readonly array $adapters)
    {}

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
