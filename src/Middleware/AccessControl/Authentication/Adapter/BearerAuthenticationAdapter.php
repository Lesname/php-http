<?php
declare(strict_types=1);

namespace LesHttp\Middleware\Authentication\Adapter;

use Override;
use LesToken\Codec\TokenCodec;
use LesValueObject\Composite\Exception\CannotParseReference;
use LesValueObject\Composite\ForeignReference;
use LesValueObject\String\Exception\TooLong;
use LesValueObject\String\Exception\TooShort;
use LesValueObject\String\Format\Exception\NotFormat;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class BearerAuthenticationAdapter implements AuthenticationAdapter
{
    private const string AUTHORIZATION_REGEXP = <<<'REGEXP'
/^Bearer (.+)$/
REGEXP;

    public function __construct(private readonly TokenCodec $codec)
    {}

    /**
     * @throws CannotParseReference
     * @throws TooLong
     * @throws TooShort
     * @throws NotFormat
     */
    #[Override]
    public function resolve(ServerRequestInterface $request): ?ForeignReference
    {
        $header = $request->getHeaderLine('authorization');

        if (preg_match(self::AUTHORIZATION_REGEXP, $header, $matches) === 1) {
            try {
                $claims = $this->codec->decode($matches[1]);
            } catch (Throwable) {
                return null;
            }

            if (!is_array($claims)) {
                return null;
            }

            if (isset($claims['identity'])) {
                assert(is_string($claims['identity']));

                return ForeignReference::fromString($claims['identity']);
            }
        }

        return null;
    }
}
