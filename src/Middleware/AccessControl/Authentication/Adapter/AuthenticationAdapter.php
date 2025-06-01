<?php
declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Authentication\Adapter;

use LesValueObject\Composite\ForeignReference;
use Psr\Http\Message\ServerRequestInterface;

interface AuthenticationAdapter
{
    public function resolve(ServerRequestInterface $request): ?ForeignReference;
}
