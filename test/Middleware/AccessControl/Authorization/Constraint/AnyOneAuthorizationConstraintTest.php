<?php

declare(strict_types=1);

namespace LesHttpTest\Middleware\AccessControl\Authorization\Constraint;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use LesHttp\Middleware\AccessControl\Authorization\Constraint\AnyOneAuthorizationConstraint;

final class AnyOneAuthorizationConstraintTest extends TestCase
{
    public function testAllowed(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $constraint = new AnyOneAuthorizationConstraint();

        self::assertTrue($constraint->isAllowed($request));
    }
}
