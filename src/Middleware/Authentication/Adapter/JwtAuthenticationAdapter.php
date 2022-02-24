<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Authentication\Adapter;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use LessValueObject\Composite\ForeignReference;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class JwtAuthenticationAdapter implements AuthenticationAdapter
{
    private const AUTHORIZATION_REGEXP = <<<'REGEXP'
/^Bearer ([a-zA-Z0-9\-_]+\.[a-zA-Z0-9\-_]+\.[a-zA-Z0-9\-_]+)$/
REGEXP;

    /**
     * @param array<array{keyMaterial: string, algorithm: string}> $keys
     */
    public function __construct(private readonly array $keys)
    {}

    public function resolve(ServerRequestInterface $request): ?ForeignReference
    {
        $header = $request->getHeaderLine('authorization');

        if (preg_match(self::AUTHORIZATION_REGEXP, $header, $matches) === 1) {
            try {
                $claims = $this->getClaims($matches[1]);
            } catch (Throwable) {
                return null;
            }

            if (isset($claims->sub)) {
                assert(is_string($claims->sub));

                return ForeignReference::fromString($claims->sub);
            }
        }

        return null;
    }

    private function getClaims(string $token): object
    {
        return JWT::decode(
            $token,
            array_map(
                function (array $settings): Key {
                    $keyMaterial = file_get_contents($settings['keyMaterial']);
                    assert(is_string($keyMaterial));

                    return new Key(
                        trim($keyMaterial),
                        $settings['algorithm'],
                    );
                },
                $this->keys,
            ),
        );
    }
}
