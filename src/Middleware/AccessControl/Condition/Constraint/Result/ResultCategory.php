<?php
declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Condition\Constraint\Result;

enum ResultCategory
{
    case Ok;
    case Conflict;
    case Constraint;

    public function getHttpCode(): int
    {
        return match ($this) {
            self::Ok => 200,
            self::Conflict => 409,
            self::Constraint => 412,
        };
    }
}
