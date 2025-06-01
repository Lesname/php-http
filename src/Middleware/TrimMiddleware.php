<?php
declare(strict_types=1);

namespace LesHttp\Middleware;

use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;

/**
 * @deprecated use Input/TrimMiddleware
 */
final class TrimMiddleware implements MiddlewareInterface
{
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $body = $request->getParsedBody();

        if (is_array($body)) {
            $body = $this->trimArray($body);
        } elseif (is_object($body)) {
            $body = $this->trimObject($body);
        }

        return $handler->handle($request->withParsedBody($body));
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    private function trimValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return $this->trimArray($value);
        }

        if (is_object($value)) {
            return $this->trimObject($value);
        }

        if (is_string($value)) {
            return $this->trimString($value);
        }

        return $value;
    }

    /**
     * @param array<mixed> $array
     *
     * @return array<mixed>
     *
     * @psalm-suppress MixedAssignment unknown value
     */
    private function trimArray(array $array): array
    {
        return array_map(
            fn (mixed $item): mixed => $this->trimValue($item),
            $array,
        );
    }

    private function trimObject(object $object): object
    {
        assert($object instanceof stdClass);

        return (object)$this->trimArray(get_object_vars($object));
    }

    private function trimString(string $string): ?string
    {
        $string = str_replace(' ', ' ', $string);
        $string = trim($string);

        return $string !== ''
            ? $string
            : null;
    }
}
