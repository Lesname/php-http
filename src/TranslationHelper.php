<?php

declare(strict_types=1);

namespace LesHttp;

final class TranslationHelper
{
    public static function getTranslationDirectory(): string
    {
        return __DIR__ . '/../resource/translation';
    }
}
