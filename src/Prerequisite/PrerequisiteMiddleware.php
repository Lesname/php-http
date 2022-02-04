<?php
declare(strict_types=1);

namespace LessHttp\Prerequisite;

use JsonException;
use LessHttp\Prerequisite\Constraint\PrerequisiteConstraint;
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
     * @param array<string, array<mixed>> $routes
     */
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly ContainerInterface $container,
        private readonly array $routes,
    ) {}

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws JsonException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();

        if (isset($this->routes[$path]['prerequisites'])) {
            $prerequisites = $this->routes[$path]['prerequisites'];
            assert(is_array($prerequisites));
            /** @var array<string> $prerequisites */

            foreach ($prerequisites as $prerequisite) {
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
