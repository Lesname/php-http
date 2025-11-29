<?php

declare(strict_types=1);

namespace LesHttp\Middleware\Locale;

use Override;
use SplPriorityQueue;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class LocaleMiddleware implements MiddlewareInterface
{
    /** @var array<string, string> */
    private readonly array $genericLocales;

    /**
     * @param array<string> $allowedLocales
     */
    public function __construct(
        private readonly string $defaultLocale,
        private readonly array $allowedLocales,
    ) {
        $genericLocales = [];

        foreach ($this->allowedLocales as $locale) {
            $key = substr($locale, 0, 2);

            if (array_key_exists($key, $genericLocales)) {
                continue;
            }

            $genericLocales[$key] = $locale;
        }

        $this->genericLocales = $genericLocales;
    }

    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request->withAttribute('useLocale', $this->detectUseLanguage($request)));
    }

    private function detectUseLanguage(ServerRequestInterface $request): string
    {
        $header = $request->getHeaderLine('Accept-Language');

        if (strlen($header) === 0) {
            return $this->defaultLocale;
        }

        $queue = new SplPriorityQueue();

        foreach (explode(',', $header) as $part) {
            $part = trim($part);

            if (
                preg_match(
                    '/^(?<locale>[a-z]{2}(-[A-Z]{2})?)(;q=(?<prio>\d+(\.\d+)?))?$/',
                    trim($part),
                    $matches,
                )
            ) {
                $queue->insert(
                    str_replace('-', '_', $matches['locale']),
                    (float)($matches['prio'] ?? 1),
                );
            }
        }

        $useLocale = $this->defaultLocale;

        foreach ($queue as $locale) {
            assert(is_string($locale));
            $length = strlen($locale);

            if ($length === 5) {
                if (in_array($locale, $this->allowedLocales)) {
                    $useLocale = $locale;

                    break;
                }

                continue;
            }

            if ($length === 2) {
                if (array_key_exists($locale, $this->genericLocales)) {
                    $useLocale = $this->genericLocales[$locale];

                    break;
                }
            }
        }

        return $useLocale;
    }
}
