<?php

declare(strict_types=1);

namespace LesHttp\Config;

use LesHttp\Router\Router;
use LesHttp\Router\RpcRouter;
use LesHttp\Router\RpcRouterFactory;
use LesHttp\Handler\MiddlewarePipeline;
use Psr\Http\Server\RequestHandlerInterface;
use LesHttp\Middleware\Input\TrimMiddleware;
use LesHttp\Handler\MiddlewarePipelineFactory;
use LesHttp\Middleware\Route\RouterMiddleware;
use LesHttp\Middleware\Response\CorsMiddleware;
use LesHttp\Middleware\Route\NoRouteMiddleware;
use LesHttp\Middleware\Locale\LocaleMiddleware;
use LesHttp\Middleware\Route\DispatchMiddleware;
use LesHttp\Middleware\Input\ValidationMiddleware;
use LesHttp\Middleware\Input\Decode\JsonMiddleware;
use LesHttp\Middleware\Analytics\AnalyticsMiddleware;
use LesHttp\Middleware\Route\RouterMiddlewareFactory;
use LesHttp\Middleware\Locale\LocaleMiddlewareFactory;
use LesHttp\Middleware\Response\CorsMiddlewareFactory;
use LesHttp\Middleware\Route\NoRouteMiddlewareFactory;
use LesHttp\Middleware\Route\DispatchMiddlewareFactory;
use LesHttp\Middleware\Response\CatchExceptionMiddleware;
use LesHttp\Middleware\Input\ValidationMiddlewareFactory;
use LesHttp\Middleware\Input\Decode\JsonMiddlewareFactory;
use LesHttp\Middleware\Analytics\AnalyticsMiddlewareFactory;
use LesHttp\Middleware\Response\CatchExceptionMiddlewareFactory;
use LesHttp\Middleware\AccessControl\Throttle\ThrottleMiddleware;
use LesHttp\Middleware\AccessControl\Condition\ConditionMiddleware;
use LesHttp\Middleware\AccessControl\Throttle\ThrottleMiddlewareFactory;
use LesHttp\Middleware\AccessControl\Condition\ConditionMiddlewareFactory;
use LesHttp\Middleware\AccessControl\Authorization\AuthorizationMiddleware;
use LesHttp\Middleware\AccessControl\Authentication\AuthenticationMiddleware;
use LesHttp\Middleware\AccessControl\Authorization\AuthorizationMiddlewareFactory;
use LesHttp\Middleware\AccessControl\Authentication\AuthenticationMiddlewareFactory;
use LesHttp\Middleware\AccessControl\Authorization\Constraint\GuestAuthorizationConstraint;
use LesHttp\Middleware\AccessControl\Authorization\Constraint\NoOneAuthorizationConstraint;
use LesHttp\Middleware\AccessControl\Authorization\Constraint\AnyOneAuthorizationConstraint;
use LesHttp\Middleware\AccessControl\Authorization\Constraint\AnyIdentityAuthorizationConstraint;

/**
 * @psalm-immutable
 */
final class ConfigProvider
{
    /**
     * @return array<string, mixed>
     *
     * @psalm-pure
     */
    public function __invoke(): array
    {
        return [
            'translator' => [
                'translation' => [
                    'nl_NL' => [
                        __DIR__ . '/../../resource/translation/nl_NL.php',
                    ],
                    'en_US' => [
                        __DIR__ . '/../../resource/translation/en_US.php',
                    ],
                ],
            ],
            'dependencies' => [
                'aliases' => [
                    Router::class => RpcRouter::class,

                    RequestHandlerInterface::class => MiddlewarePipeline::class,
                ],
                'invokables' => [
                    AnyIdentityAuthorizationConstraint::class => AnyIdentityAuthorizationConstraint::class,
                    AnyOneAuthorizationConstraint::class => AnyOneAuthorizationConstraint::class,
                    GuestAuthorizationConstraint::class => GuestAuthorizationConstraint::class,
                    NoOneAuthorizationConstraint::class => NoOneAuthorizationConstraint::class,

                    TrimMiddleware::class => TrimMiddleware::class,
                ],
                'factories' => [
                    RpcRouter::class => RpcRouterFactory::class,

                    MiddlewarePipeline::class => MiddlewarePipelineFactory::class,

                    JsonMiddleware::class => JsonMiddlewareFactory::class,
                    AuthenticationMiddleware::class => AuthenticationMiddlewareFactory::class,
                    AuthorizationMiddleware::class => AuthorizationMiddlewareFactory::class,
                    ConditionMiddleware::class => ConditionMiddlewareFactory::class,
                    ThrottleMiddleware::class => ThrottleMiddlewareFactory::class,
                    AnalyticsMiddleware::class => AnalyticsMiddlewareFactory::class,
                    ValidationMiddleware::class => ValidationMiddlewareFactory::class,
                    LocaleMiddleware::class => LocaleMiddlewareFactory::class,
                    CatchExceptionMiddleware::class => CatchExceptionMiddlewareFactory::class,
                    CorsMiddleware::class => CorsMiddlewareFactory::class,
                    DispatchMiddleware::class => DispatchMiddlewareFactory::class,
                    NoRouteMiddleware::class => NoRouteMiddlewareFactory::class,
                    RouterMiddleware::class => RouterMiddlewareFactory::class,
                ],
            ],
            MiddlewarePipeline::class => [
                CorsMiddleware::class,
                CatchExceptionMiddleware::class,
                AuthenticationMiddleware::class,
                AnalyticsMiddleware::class,
                ThrottleMiddleware::class,
                RouterMiddleware::class,
                NoRouteMiddleware::class,
                JsonMiddleware::class,
                TrimMiddleware::class,
                LocaleMiddleware::class,
                ValidationMiddleware::class,
                AuthorizationMiddleware::class,
                ConditionMiddleware::class,
                DispatchMiddleware::class,
            ],
        ];
    }
}
