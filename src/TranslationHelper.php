<?php

declare(strict_types=1);

namespace LesHttp;

/**
 * @psalm-pure
 *
 * @deprecated done via ConfigProvider
 */
final class TranslationHelper
{
    /**
     * @psalm-pure
     */
    public static function getTranslationDirectory(): string
    {
        return __DIR__ . '/../resource/translation';
    }
}
