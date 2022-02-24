<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Authorization;

use LessHttp\Middleware\Authorization\Constraint\AuthorizationConstraint;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AuthorizationMiddleware implements MiddlewareInterface
{
    /**
     * @param array<string, array<mixed>> $routes
     */
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly ContainerInterface $container,
        private readonly array $routes,
    ) {}

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();

        if (isset($this->routes[$path]['authorizations'])) {
            $authorizations = $this->routes[$path]['authorizations'];
            assert(is_array($authorizations));
            /** @var array<string> $authorizations */

            if (!$this->isAllowed($request, $authorizations)) {
                return $this
                    ->responseFactory
                    ->createResponse(403);
            }
        }

        return $handler->handle($request);
    }

    /**
     * @param ServerRequestInterface $request
     * @param array<string> $authorizations
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function isAllowed(ServerRequestInterface $request, array $authorizations): bool
    {
        foreach ($authorizations as $authorization) {
            $constraint = $this->container->get($authorization);
            assert($constraint instanceof AuthorizationConstraint);

            if ($constraint->isAllowed($request)) {
                return true;
            }
        }

        return false;
    }
}
