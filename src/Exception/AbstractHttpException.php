<?php

declare(strict_types=1);

namespace LesHttp\Exception;

use Exception;

/**
 * @psalm-immutable
 *
 * @psalm-suppress MutableDependency
 */
abstract class AbstractHttpException extends Exception
{
}
