<?php

declare(strict_types=1);

namespace LesHttp\Router\Route\Exception;

use LesHttp\Exception\AbstractHttpException;

/**
 * @psalm-immutable
 */
final class OptionNotSet extends AbstractHttpException
{
    public function __construct(public readonly string $key)
    {
        parent::__construct("Option '{$key}' is not set");
    }
}
