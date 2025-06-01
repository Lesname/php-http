<?php
declare(strict_types=1);

namespace LesHttp\Router;

use Override;
use LesHttp\Router\Route\Route;
use LesHttp\Router\Route\ArrayRoute;
use Psr\Http\Message\RequestInterface;
use LesDocumentor\Route\Document\Property\Method;

final class RpcRouter implements Router
{
    /**
     * @param array<string, array<mixed>> $routes
     */
    public function __construct(private readonly array $routes)
    {}

    #[Override]
    public function match(RequestInterface $request): ?Route
    {
        $method = strtolower($request->getMethod());
        $path = $request->getUri()->getPath();

        if (isset($this->routes["{$method}:{$path}"])) {
            return new ArrayRoute($this->routes["{$method}:{$path}"]);
        }

        if ($method === Method::Post->value) {
            $tryMethods = [
                Method::Query->value,
                Method::Delete->value,
                Method::Patch->value,
                Method::Put->value,
            ];

            foreach ($tryMethods as $tryMethod) {
                if (isset($this->routes["{$tryMethod}:{$path}"])) {
                    return new ArrayRoute($this->routes["{$tryMethod}:{$path}"]);
                }
            }
        }

        return null;
    }
}
