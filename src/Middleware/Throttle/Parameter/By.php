<?php
declare(strict_types=1);

namespace LesHttp\Middleware\Throttle\Parameter;

/**
 * @deprecated moved into AccessControl namespace
 */
enum By: string
{
    case Identity = 'identity';
    case Guest = 'guest';
}
