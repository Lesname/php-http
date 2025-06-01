<?php
declare(strict_types=1);

namespace LesHttp\Router\Route\Exception;

use Exception;

/**
 * @psalm-immutable
 */
final class OptionNotSet extends Exception
{
    public function __construct(public readonly string $key)
    {
        parent::__construct("Option '{$key}' is not set");
    }
}
