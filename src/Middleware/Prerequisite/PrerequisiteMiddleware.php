<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Prerequisite;

use JsonException;
use LessHttp\Middleware\Prerequisite\Constraint\PrerequisiteConstraint;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;

final class PrerequisiteMiddleware implements MiddlewareInterface
{
    /**
     * @param array<string, array<string>> $prerequisites
     */
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly ContainerInterface $container,
        private readonly array $prerequisites,
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

        if (isset($this->prerequisites[$key])) {
            foreach ($this->prerequisites[$key] as $prerequisite) {
                $constraint = $this->container->get($prerequisite);
                assert($constraint instanceof PrerequisiteConstraint);

                if (!$constraint->isSatisfied($request)) {
                    $name = lcfirst((new ReflectionClass($constraint))->getShortName());

                    $json = json_encode(
                        [
                            'message' => 'Prerequisite failed',
                            'code' => "prerequisite.{$name}",
                        ],
                        JSON_THROW_ON_ERROR,
                    );

                    return $this
                        ->responseFactory
                        ->createResponse(409)
                        ->withBody($this->streamFactory->createStream($json))
                        ->withHeader('content-type', 'application/json');
                }
            }
        }

        return $handler->handle($request);
    }
}
