<?php
declare(strict_types=1);

namespace LesHttpTest\Middleware\Authorization\Constraint;

use Psr\Http\Message\ServerRequestInterface;
use LesHttp\Middleware\Authorization\Constraint\NoOneAuthorizationConstraint;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LesHttp\Middleware\Authorization\Constraint\NoOneAuthorizationConstraint
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
