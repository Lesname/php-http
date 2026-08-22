<?php

declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Authentication;

use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use LesHttp\Middleware\AccessControl\Authentication\Adapter\Identity;
use LesHttp\Middleware\AccessControl\Authentication\Adapter\AuthenticationAdapter;

final class AuthenticationMiddleware implements MiddlewareInterface
{
    /**
     * @param array<AuthenticationAdapter> $adapters
     *
     * @psalm-mutation-free
     */
    public function __construct(private readonly array $adapters)
    {}

    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        foreach ($this->adapters as $adapter) {
            $identity = $adapter->resolve($request);

            if ($identity instanceof Identity) {
                $request = $request
                    ->withAttribute('identity', $identity->reference)
                    ->withAttribute('identity.reference', $identity->reference)
                    ->withAttribute('identity.context', $identity->context);

                break;
            }
        }

        return $handler->handle($request);
    }
}
