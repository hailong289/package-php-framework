<?php

namespace Hola\Transport;

use Hola\Container\Container;
use Hola\Exceptions\AppException;

class MiddlewareBuilder {

    public function handle($params, Container $container) {
        foreach ($params as $middleware) {
            if (!class_exists($middleware)) {
                throw new AppException("Middleware '$middleware' does not exit", 500);
            }

            $result = $container->make($middleware)->run();
            if (isset($result['status'])) {
                if (!empty($result['status'])) {
                    $container->replace(Request::class, function () use ($result) {
                        return $result['request'];
                    });
                    continue;
                }
                return Response::json($result, $result['code']);
            }
            return $result;
        }
        return ["passable" => true];
    }
}