<?php
declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Throttle\Parameter;

enum By: string
{
    case Identity = 'identity';
    case Guest = 'guest';
}
