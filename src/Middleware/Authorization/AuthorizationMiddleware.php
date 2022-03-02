<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Authorization;

use JsonException;
use LessHttp\Middleware\Authorization\Constraint\AuthorizationConstraint;
use LessHttp\Response\ErrorResponse;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AuthorizationMiddleware implements MiddlewareInterface
{
    /**
     * @param array<string, array<string>> $authorizations
     */
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly ContainerInterface $container,
        private readonly array $authorizations,
    ) {}

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws JsonException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        $key = "{$method}:{$path}";

        if (isset($this->authorizations[$key])) {
            $authorizations = $this->authorizations[$key];

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
                    ->withBody($stream);
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
