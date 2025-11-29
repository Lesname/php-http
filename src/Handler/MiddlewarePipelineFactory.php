<?php

declare(strict_types=1);

namespace LesHttp\Handler;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;

final class MiddlewarePipelineFactory
{
    public function __invoke(ContainerInterface $container): MiddlewarePipeline
    {
        $config = $container->get('config');

        assert(is_array($config));
        assert(is_array($config[MiddlewarePipeline::class]));

        $handlers = [];

        foreach ($config[MiddlewarePipeline::class] as $handler) {
            assert(is_string($handler));

            $handler = $container->get($handler);
            assert($handler instanceof MiddlewareInterface);

            $handlers[] = $handler;
        }

        return new MiddlewarePipeline($handlers);
    }
}
