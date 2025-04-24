<?php
declare(strict_types=1);

namespace LesHttpTest\Middleware\Authorization\Constraint;

use LesHttp\Middleware\Authorization\Constraint\AnyOneAuthorizationConstraint;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @covers \LesHttp\Middleware\Authorization\Constraint\AnyOneAuthorizationConstraint
 */
final class AnyOneAuthorizationConstraintTest extends TestCase
{
    public function testAllowed(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $constraint = new AnyOneAuthorizationConstraint();

        self::assertTrue($constraint->isAllowed($request));
    }
}
