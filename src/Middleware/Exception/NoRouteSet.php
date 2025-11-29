<?php

declare(strict_types=1);

namespace LesHttp\Middleware\Exception;

use LesHttp\Exception\AbstractHttpException;

/**
 * @psalm-immutable
 */
final class NoRouteSet extends AbstractHttpException
{
}
