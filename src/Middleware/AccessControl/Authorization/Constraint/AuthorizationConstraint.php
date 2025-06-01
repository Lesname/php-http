<?php
declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Authorization\Constraint;

use Psr\Http\Message\ServerRequestInterface;

interface AuthorizationConstraint
{
    public function isAllowed(ServerRequestInterface $request): bool;
}
