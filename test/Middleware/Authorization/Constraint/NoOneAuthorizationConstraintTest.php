<?php
declare(strict_types=1);

namespace LessHttpTest\Middleware\Authorization\Constraint;

use Psr\Http\Message\ServerRequestInterface;
use LessHttp\Middleware\Authorization\Constraint\NoOneAuthorizationConstraint;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LessHttp\Middleware\Authorization\Constraint\NoOneAuthorizationConstraint
 */
class NoOneAuthorizationConstraintTest extends TestCase
{
    public function testAllowed(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $constraint = new NoOneAuthorizationConstraint();

        self::assertFalse($constraint->isAllowed($request));
    }
}
