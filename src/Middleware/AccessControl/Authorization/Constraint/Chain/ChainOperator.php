<?php

declare(strict_types=1);

namespace LesHttp\Middleware\AccessControl\Authorization\Constraint\Chain;

enum ChainOperator
{
    case And;
    case Or;
}
