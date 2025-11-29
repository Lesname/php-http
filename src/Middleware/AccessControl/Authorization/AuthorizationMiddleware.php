<?php

declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Authorization;

use Override;
use JsonException;
use LesHttp\Router\Route\Route;
use LesHttp\Response\ErrorResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use LesHttp\Middleware\Exception\NoRouteSet;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use LesHttp\Router\Route\Exception\OptionNotSet;
use LesHttp\Middleware\AccessControl\Authorization\Constraint\AuthorizationConstraint;

final class AuthorizationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly ContainerInterface $container,
    ) {}

    /**
     * @throws ContainerExceptionInterface
     * @throws JsonException
     * @throws NoRouteSet
     * @throws NotFoundExceptionInterface
     * @throws OptionNotSet
     */
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $request->getAttribute('route');

        if (!$route instanceof Route) {
            throw new NoRouteSet();
        }

        $authorizations = $route->getOption('authorizations');
        assert(is_array($authorizations));

        if (!$this->isAllowed($request, $authorizations)) {
            $stream = $this->streamFactory->createStream(
                json_encode(
                    new ErrorResponse(
                        'Not authorized to execute request',
                        'notAuthorized',
                    ),
                    flags: JSON_THROW_ON_ERROR,
                ),
            );

            return $this
                ->responseFactory
                ->createResponse(403)
                ->withAddedHeader('content-type', 'application/json')
                ->withBody($stream);
        }

        return $handler->handle($request);
    }

    /**
     * @param array<mixed> $authorizations
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function isAllowed(ServerRequestInterface $request, array $authorizations): bool
    {
        foreach ($authorizations as $authorization) {
            assert(is_string($authorization));

            $constraint = $this->container->get($authorization);
            assert($constraint instanceof AuthorizationConstraint);

            if ($constraint->isAllowed($request)) {
                return true;
            }
        }

        return false;
    }
}
