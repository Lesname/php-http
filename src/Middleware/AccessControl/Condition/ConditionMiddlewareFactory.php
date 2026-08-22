<?php

declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Condition;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ConditionMiddlewareFactory
{
    public function __invoke(ContainerInterface $container): ConditionMiddleware
    {
        $responseFactory = $container->get(ResponseFactoryInterface::class);
        assert($responseFactory instanceof ResponseFactoryInterface);

        $streamFactory = $container->get(StreamFactoryInterface::class);
        assert($streamFactory instanceof StreamFactoryInterface);

        $translator = $container->get(TranslatorInterface::class);
        assert($translator instanceof TranslatorInterface);

        return new ConditionMiddleware($responseFactory, $streamFactory, $translator, $container);
    }
}
