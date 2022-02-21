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
    public function __construct(private readonly AuthenticationAdapter $adapter)
    {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $result = $this->adapter->resolve($request);

        if ($result) {
            $request = $request->withAttribute('identity', $result);
        }

        return $handler->handle($request);
    }
}
