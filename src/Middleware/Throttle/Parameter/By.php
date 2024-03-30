<?php
declare(strict_types=1);

namespace LessHttp\Middleware\Throttle\Parameter;

enum By: string
{
    case Identity = 'identity';
    /** @deprecated use Guest */
    case Ip = 'ip';
    case Guest = 'guest';
}
