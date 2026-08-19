<?php

declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Authorization\Exception;

use LesHttp\Exception\AbstractHttpException;

/**
 * @psalm-immutable
 */
final class UnhandledConstraint extends AbstractHttpException
{
    public function __construct(public readonly mixed $constraint)
    {
        parent::__construct(
            sprintf(
                'Unhandled constraint "%s"',
                get_debug_type($constraint),
            ),
        );
    }
}
