<?php

declare(strict_types=1);

namespace LesHttpTest\Middleware\AccessControl\Authorization\Constraint;

use Psr\Http\Message\ServerRequestInterface;
use LesValueObject\Composite\ForeignReference;
use LesValueObject\String\Format\Resource\Type;
use LesValueObject\String\Format\Resource\Identifier;
use LesHttp\Middleware\AccessControl\Authorization\Constraint\SelfAuthorizationConstraint;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SelfAuthorizationConstraint::class)]
class SelfAuthorizationConstraintTest extends TestCase
{
    public function testAllowed(): void
    {
        $identityReference = new ForeignReference(
            new Type('foo'),
            new Identifier('7b38d184-a873-4821-bd38-5440752fe91e'),
        );

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects(self::once())
            ->method('getAttribute')
            ->with('identity.reference')
            ->willReturn($identityReference);

        $request
            ->expects(self::once())
            ->method('getParsedBody')
            ->willReturn(['id' => '7b38d184-a873-4821-bd38-5440752fe91e']);

        self::assertTrue((new SelfAuthorizationConstraint())->isAllowed($request));
    }

    public function testNotAllowedDifferentIdentity(): void
    {
        $identityReference = new ForeignReference(
            new Type('foo'),
            new Identifier('7b38d184-a873-4821-bd38-5440752fe91b'),
        );

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects(self::once())
            ->method('getAttribute')
            ->with('identity.reference')
            ->willReturn($identityReference);

        $request
            ->expects(self::once())
            ->method('getParsedBody')
            ->willReturn(['id' => '7b38d184-a873-4821-bd38-5440752fe91e']);

        self::assertFalse((new SelfAuthorizationConstraint())->isAllowed($request));
    }

    public function testNotAllowedNoBodyId(): void
    {
        $identityReference = new ForeignReference(
            new Type('foo'),
            new Identifier('7b38d184-a873-4821-bd38-5440752fe91b'),
        );

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects(self::once())
            ->method('getAttribute')
            ->with('identity.reference')
            ->willReturn($identityReference);

        $request
            ->expects(self::once())
            ->method('getParsedBody')
            ->willReturn([]);

        self::assertFalse((new SelfAuthorizationConstraint())->isAllowed($request));
    }

    public function testNotAllowedNoIdentityReference(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects(self::once())
            ->method('getAttribute')
            ->with('identity.reference')
            ->willReturn(null);

        $request
            ->expects(self::never())
            ->method('getParsedBody');

        self::assertFalse((new SelfAuthorizationConstraint())->isAllowed($request));
    }
}
