<?php

declare(strict_types=1);

namespace LesHttp\Middleware\Route\Exception;

use LesHttp\Exception\AbstractHttpException;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @psalm-immutable
 */
final class NoHandler extends AbstractHttpException
{
    /**
     * @psalm-mutation-free
     */
    public function __construct(public readonly ServerRequestInterface $request)
    {
        parent::__construct(
            sprintf(
                'No handler found for "%s"',
                $this->request->getUri()->getPath(),
            ),
        );
    }
}
