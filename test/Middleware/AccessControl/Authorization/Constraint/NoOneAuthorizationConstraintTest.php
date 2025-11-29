<?php

declare(strict_types=1);

namespace LesHttpTest\Middleware\AccessControl\Authorization\Constraint;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use LesHttp\Middleware\AccessControl\Authorization\Constraint\NoOneAuthorizationConstraint;

class NoOneAuthorizationConstraintTest extends TestCase
{
    public function testAllowed(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $constraint = new NoOneAuthorizationConstraint();

        self::assertFalse($constraint->isAllowed($request));
    }
}
