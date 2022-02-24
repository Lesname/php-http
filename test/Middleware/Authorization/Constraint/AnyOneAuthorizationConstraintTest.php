<?php
declare(strict_types=1);

namespace LessHttpTest\Middleware\Authorization\Constraint;

use LessHttp\Middleware\Authorization\Constraint\AnyOneAuthorizationConstraint;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @covers \LessHttp\Middleware\Authorization\Constraint\AnyOneAuthorizationConstraint
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
