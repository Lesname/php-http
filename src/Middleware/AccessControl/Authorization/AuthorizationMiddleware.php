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
use LesHttp\Middleware\AccessControl\Authorization\Exception\UnhandledConstraint;
use LesHttp\Middleware\AccessControl\Authorization\Constraint\Chain\ChainOperator;
use LesHttp\Middleware\AccessControl\Authorization\Constraint\AuthorizationConstraint;
use LesHttp\Middleware\AccessControl\Authorization\Constraint\Chain\AuthorizationConstraintChain;

final class AuthorizationMiddleware implements MiddlewareInterface
{
    /**
     * @psalm-mutation-free
     */
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
     * @throws UnhandledConstraint
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

        if (!$this->isAnyAllowed($request, $authorizations)) {
            $stream = $this
                ->streamFactory
                ->createStream(
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
     * @param array<mixed> $constraints
     *
     * @throws UnhandledConstraint
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function isAnyAllowed(ServerRequestInterface $request, array $constraints): bool
    {
        return array_any($constraints, fn($constraint) => $this->isConstraintAllowed($request, $constraint));
    }

    /**
     * @param array<mixed> $constraints
     *
     * @throws UnhandledConstraint
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function areAllAllowed(ServerRequestInterface $request, array $constraints): bool
    {
        return array_all($constraints, fn($constraint) => $this->isConstraintAllowed($request, $constraint));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws UnhandledConstraint
     */
    private function isConstraintAllowed(ServerRequestInterface $request, mixed $constraint): bool
    {
        if (is_string($constraint)) {
            $constraint = $this->container->get($constraint);
        }

        if ($constraint instanceof AuthorizationConstraint) {
            return $constraint->isAllowed($request);
        }

        if ($constraint instanceof AuthorizationConstraintChain) {
            return match ($constraint->operator) {
                ChainOperator::And => $this->areAllAllowed($request, $constraint->constraints),
                ChainOperator::Or => $this->isAnyAllowed($request, $constraint->constraints),
            };
        }

        throw new UnhandledConstraint($constraint);
    }
}
