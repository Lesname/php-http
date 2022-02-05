<?php
declare(strict_types=1);

namespace LessHttp\Throttle;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

final class ThrottleMiddleware implements MiddlewareInterface
{
    /**
     * @param ResponseFactoryInterface $responseFactory
     * @param Connection $connection
     * @param array<array{duration: int, points: int}> $limits
     */
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly Connection $connection,
        private readonly array $limits,
    ) {
        assert(count($limits) > 0);
    }

    /**
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->isThrottled($request)) {
            return $this
                ->responseFactory
                ->createResponse(429);
        }

        try {
            $response = $handler->handle($request);
        } catch (Throwable $e) {
            $this->logRequest($request);

            throw $e;
        }

        $this->logRequest($request, $response);

        return $response;
    }

    /**
     * @throws Exception
     *
     * @psalm-suppress MixedAssignment
     */
    private function isThrottled(ServerRequestInterface $request): bool
    {
        $action = $this->getActionFromRequest($request);
        $identity = $this->getIdentityFromRequest($request);
        $ip = $this->getIpFromRequest($request);

        $pointSelect = <<<'SQL'
SUM(
    case
        when floor(response / 100) = 2 THEN 1
        when floor(response / 100) = 4 then 3
        when floor(response / 100) = 5 then 2
        else 5
    end
)
SQL;

        $now = (int)floor(microtime(true) * 1_000);

        foreach ($this->limits as $limit) {
            $builder = $this->connection->createQueryBuilder();
            $points = $builder
                ->select($pointSelect)
                ->from('throttle_request')
                ->andWhere('action = :action')
                ->setParameter('action', $action)
                ->andWhere('identity = :identity')
                ->setParameter('identity', $identity)
                ->andWhere('ip = :ip')
                ->setParameter('ip', $ip)
                ->andWhere('requested_on >= :since')
                ->setParameter('since', $now - $limit['duration'])
                ->fetchOne();

            assert(is_string($points) && ctype_digit($points));

            if ($points >= $limit['points']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws Exception
     */
    private function logRequest(ServerRequestInterface $request, ?ResponseInterface $response = null): void
    {
        $builder = $this->connection->createQueryBuilder();
        $builder
            ->insert('throttle_request')
            ->values(
                [
                    'action' => ':action',
                    'identity' => ':identity',
                    'ip' => ':identity',
                    'requested_on' => ':ip',
                    'response' => ':response',
                ],
            )
            ->setParameters(
                [
                    'action' => $this->getActionFromRequest($request),
                    'identity' => $this->getIdentityFromRequest($request),
                    'ip' => $this->getIpFromRequest($request),
                    'requested_on' => (int)floor(microtime(true) * 1000),
                    'response' => $response ? $response->getStatusCode() : 500,
                ],
            )
            ->executeStatement();
    }

    private function getActionFromRequest(ServerRequestInterface $request): string
    {
        $path = $request->getUri()->getPath();
        $position = strrpos($path, '/');

        $action = is_int($position)
            ? substr($path, $position + 1)
            : $path;

        return substr($action, 0, 60);
    }

    private function getIdentityFromRequest(ServerRequestInterface $request): ?string
    {
        $identity = $request->getAttribute('identity');
        assert($identity === null || is_string($identity));

        return $identity;
    }

    private function getIpFromRequest(ServerRequestInterface $request): ?string
    {
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? null;
        assert(is_string($ip) || is_null($ip));

        return $ip;
    }
}
