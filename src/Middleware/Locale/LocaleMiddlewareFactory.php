<?php

declare(strict_types=1);

namespace LesHttp\Middleware\Locale;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;

final class LocaleMiddlewareFactory
{
    /**
     * @psalm-suppress MixedArgumentTypeCoercion
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): LocaleMiddleware
    {
        $config = $container->get('config');
        assert(is_array($config));

        $settings = $config[LocaleMiddleware::class];
        assert(is_array($settings));

        assert(is_string($settings['defaultLocale']));
        assert(is_array($settings['allowedLocales']));

        // @phpstan-ignore argument.type
        return new LocaleMiddleware($settings['defaultLocale'], $settings['allowedLocales']);
    }
}
