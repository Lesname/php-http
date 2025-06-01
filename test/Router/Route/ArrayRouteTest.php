<?php
declare(strict_types=1);

namespace LesHttpTest\Router\Route;

use LesHttp\Router\Route\ArrayRoute;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use LesHttp\Router\Route\Exception\OptionNotSet;

#[CoversClass(ArrayRoute::class)]
class ArrayRouteTest extends TestCase
{
    public function testOptions(): void
    {
        $route = new ArrayRoute(['foo' => 'bar']);

        self::assertTrue($route->hasOption('foo'));
        self::assertFalse($route->hasOption('bar'));

        self::assertSame('bar', $route->getOption('foo'));
    }

    public function testMissingOptionsThrows(): void
    {
        $this->expectException(OptionNotSet::class);

        $route = new ArrayRoute(['foo' => 'bar']);
        $route->getOption('bar');
    }
}
