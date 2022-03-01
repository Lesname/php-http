<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Analytics;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use JsonException;
use LessDatabase\Query\Builder\Applier\Values\InsertValuesApplier;
use LessDatabase\Query\Builder\Applier\Values\UpdateValuesApplier;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

final class AnalyticsMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly Connection $connection,
        private readonly string $service,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (in_array(strtolower($request->getMethod()), ['options', 'head'], true)) {
            return $handler->handle($request);
        }

        try {
            $response = $handler->handle($request);
        } catch (Throwable $e) {
            $this->log($e, $request);

            throw $e;
        }

        $this->log($response, $request);

        return $response;
    }

    /**
     * @throws Exception
     * @throws JsonException
     */
    private function log(ResponseInterface | Throwable $result, ServerRequestInterface $request): void
    {
        $startTime = $this->getStartTimeFromRequest($request);

        if ($result instanceof Throwable) {
            $error = json_encode(['throwable' => $result->getMessage()], JSON_THROW_ON_ERROR);
            $response = 500;
        } else {
            $response = $result->getStatusCode();

            if ($response >= 400) {
                $error = strtolower($result->getHeaderLine('content-type')) !== 'application/json'
                    ? json_encode((string)$result->getBody(), JSON_THROW_ON_ERROR)
                    : (string)$result->getBody();
            } else {
                $error = null;
            }
        }

        $builder = InsertValuesApplier
            ::forValues(
                [
                    'service' => $this->service,
                    'action' => $this->getAction($request),

                    'identity' => $this->getIdentityFromRequest($request),

                    'ip' => $this->getIpFromRequest($request),
                    'user_agent' => $this->getUserAgentFromRequest($request),

                    'requested_on' => $startTime,
                    'duration' => (int)floor($startTime * 1000) - $startTime,

                    'response' => $response,
                    'error' => $error,
                ]
            )
            ->apply($this->connection->createQueryBuilder());

        $builder
            ->insert('request')
            ->executeStatement();
    }

    private function getAction(ServerRequestInterface $request): string
    {
        $path = $request->getUri()->getPath();
        $position = strrpos($path, '/');

        $action = is_int($position)
            ? substr($path, $position + 1)
            : $path;

        return substr($action, 0, 60);
    }

    private function getIpFromRequest(ServerRequestInterface $request): ?string
    {
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? null;
        assert(is_string($ip) || is_null($ip));

        return $ip;
    }

    private function getUserAgentFromRequest(ServerRequestInterface $request): ?string
    {
        $userAgent = $request->getHeaderLine('user-agent');

        return mb_substr(trim($userAgent), 0, 255) ?: null;
    }

    private function getIdentityFromRequest(ServerRequestInterface $request): ?string
    {
        $identity = $request->getAttribute('identity');
        assert($identity === null || is_string($identity));

        return $identity;
    }

    private function getStartTimeFromRequest(ServerRequestInterface $request): int
    {
        $startTime = $request->getServerParams()['REQUEST_TIME_FLOAT'];
        assert(is_float($startTime));

        return (int)floor($startTime * 1000);
    }
}
