<?php
declare(strict_types=1);

namespace LesHttpTest\Handler;

use LesHttp\Handler\MiddlewarePipeline;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use LesHttp\Handler\Exception\NoHandlers;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MiddlewarePipeline::class)]
class MiddlewarePipelineTest extends TestCase
{
    public function testNoHandlers(): void
    {
        $this->expectException(NoHandlers::class);

        $pipeline = new MiddlewarePipeline([]);

        $request = $this->createMock(ServerRequestInterface::class);

        $pipeline->handle($request);
    }

    public function testNext(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $response = $this->createMock(ResponseInterface::class);

        $handler = $this->createMock(MiddlewareInterface::class);
        $handler
            ->expects(self::once())
            ->method('process')
            ->with($request)
            ->willReturn($response);

        $pipeline = new MiddlewarePipeline([$handler]);

        self::assertSame($response, $pipeline->handle($request));
    }
}
