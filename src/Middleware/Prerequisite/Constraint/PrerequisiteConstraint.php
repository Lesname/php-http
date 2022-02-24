<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Prerequisite\Constraint;

use Psr\Http\Message\ServerRequestInterface;

interface PrerequisiteConstraint
{
    public function isSatisfied(ServerRequestInterface $request): bool;
}
