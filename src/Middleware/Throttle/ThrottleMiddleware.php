<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Throttle;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use JsonException;
use LessHttp\Middleware\Throttle\Parameter\By;
use LessDatabase\Query\Builder\Applier\Values\InsertValuesApplier;
use LessHttp\Response\ErrorResponse;
use LessValueObject\Composite\ForeignReference;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

final class ThrottleMiddleware implements MiddlewareInterface
{
    /**
     * @param ResponseFactoryInterface $responseFactory
     * @param Connection $connection
     * @param array<array{duration: int, points: int, action?: string, by?: By}> $limits
     */
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly Connection $connection,
        private readonly array $limits,
        private readonly int $usageModifier,
    ) {
        assert(count($limits) > 0);
    }

    /**
     * @throws Exception
     * @throws JsonException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->isThrottled($request)) {
            $stream = $this->streamFactory->createStream(
                json_encode(
                    new ErrorResponse(
                        'Too many requests',
                        'throttle.tooManyRequests',
                    ),
                    flags: JSON_THROW_ON_ERROR,
                ),
            );

            return $this
                ->responseFactory
                ->createResponse(429)
                ->withAddedHeader('content-type', 'application/json')
                ->withBody($stream);
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
        if ($this->isThrottledByLimits($request)) {
            return true;
        }

        if ($this->isThrottledByUsage()) {
            return true;
        }

        return false;
    }

    /**
     * @throws Exception
     */
    private function isThrottledByLimits(ServerRequestInterface $request): bool
    {
        $identity = $this->getIdentityFromRequest($request);
        $ip = $this->getIpFromRequest($request);

        $pointSelect = <<<'SQL'
coalesce(
    SUM(
        case
            when floor(response / 100) = 2 THEN 1
            when floor(response / 100) = 4 then 7
            when floor(response / 100) = 5 then 3
            else 5
        end
    ),
    '0'
)
SQL;

        $now = (int)floor(microtime(true) * 1_000);

        $builder = $this->connection->createQueryBuilder();
        $builder->select($pointSelect)->from('throttle_request');

        if ($identity !== null) {
            $builder->andWhere('identity = :identity')->setParameter('identity', $identity);
        } elseif ($ip !== null) {
            $builder->andWhere('ip = :ip')->setParameter('ip', $ip);
        } else {
            return false;
        }

        foreach ($this->limits as $limit) {
            $limitBuilder = clone $builder;

            if (isset($limit['action'])) {
                if ($limit['action'] !== $this->getActionFromRequest($request)) {
                    continue;
                }

                $limitBuilder
                    ->andWhere('action = :action')
                    ->setParameter('action', $this->getActionFromRequest($request));
            }

            if (isset($limit['by'])) {
                if (
                    ($limit['by'] === By::Identity && $identity === null)
                    ||
                    ($limit['by'] === By::Guest && $identity !== null)
                ) {
                    continue;
                }
            }

            $points = $limitBuilder
                ->andWhere('requested_on >= :since')
                ->setParameter('since', $now - ($limit['duration'] * 1_000))
                ->fetchOne();

            assert((is_string($points) && ctype_digit($points)) || is_int($points));

            if ($points >= $limit['points']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws Exception
     */
    private function isThrottledByUsage(): bool
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->from('throttle_request', 'tr');

        $historyUsageBuilder = clone $builder;
        $historyUsageBuilder->select("greatest(coalesce(sum(((floor(response / 100) = 4) * 4) + ((floor(response / 100) != 4))), 0) / 4, 500) * {$this->usageModifier}");

        $currentUsageBuilder = clone $builder;
        $currentUsageBuilder->select('coalesce(sum(((floor(response / 100) = 4) * 4) + ((floor(response / 100) != 4))), 0)');

        $currentUsageBuilder->andWhere('tr.requested_on >= (UNIX_TIMESTAMP() - 900) * 1000');

        for ($day = 1; $day <= 7; $day += 1) {
            $whereRange = <<<SQL
(
    tr.requested_on >= (UNIX_TIMESTAMP() - (86400 * {$day}) - 900) * 1000
    AND
    tr.requested_on <= (UNIX_TIMESTAMP() - (86400 * {$day}) + 900) * 1000
)
SQL;
            $historyUsageBuilder->orWhere($whereRange);
        }

        return $historyUsageBuilder->fetchOne() <= $currentUsageBuilder->fetchOne();
    }

    /**
     * @throws Exception
     */
    private function logRequest(ServerRequestInterface $request, ?ResponseInterface $response = null): void
    {
        if (strtolower($request->getMethod()) === 'options') {
            return;
        }

        $builder = $this->connection->createQueryBuilder();
        InsertValuesApplier
            ::forValues(
                [
                    'action' => $this->getActionFromRequest($request),
                    'identity' => $this->getIdentityFromRequest($request),
                    'ip' => $this->getIpFromRequest($request),
                    'requested_on' => (int)floor(microtime(true) * 1000),
                    'response' => $response ? $response->getStatusCode() : 500,
                ],
            )
            ->apply($builder)
            ->insert('throttle_request')
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
        assert($identity === null || $identity instanceof ForeignReference);

        return $identity instanceof ForeignReference
            ? (string)$identity
            : null;
    }

    private function getIpFromRequest(ServerRequestInterface $request): ?string
    {
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? null;
        assert(is_string($ip) || is_null($ip));

        return $ip;
    }
}
