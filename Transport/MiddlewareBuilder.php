<?php

namespace Hola\Transport;
use Hola\Application;
use Hola\Exceptions\AppException;

class MiddlewareBuilder {

    public function handle($callback) {
        $key_middlware = concat('', 'passable', PROJECT_KEY);
        /** @var Application $app */
        [$middlewares, $app] = $callback();
        $pipeline = array_reduce(
            array_reverse($middlewares),
            function ($next, $middleware) {
                return function ($request) use ($middleware, $next) {
                    return app()->make($middleware)->run($request, $next);
                };
            },
            function () {
                return true;
            } 
        );
        $closure = $pipeline($app->request());
        if ($closure instanceof ResponseBuilder) {
            return [$key_middlware => false, 'return' => $closure];
        }
        return [$key_middlware => true];
    }

}