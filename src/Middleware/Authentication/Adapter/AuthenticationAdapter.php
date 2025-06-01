<?php
declare(strict_types=1);

namespace LesHttp\Middleware\Authentication\Adapter;

use LesValueObject\Composite\ForeignReference;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @deprecated moved into AccessControl namespace
 */
interface AuthenticationAdapter
{
    public function resolve(ServerRequestInterface $request): ?ForeignReference;
}
