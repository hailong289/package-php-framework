<?php

namespace Hola\Transport;
use App\Http\Middleware\Kernel;
use Hola\Application;
use Hola\Exceptions\AppException;

class MiddlewareBuilder {

    public function handle($params, Application $app) {
        $params = $this->resolveRequiredMiddleware($params);
        if (empty($params)) {
            return [concat('', 'passable', PROJECT_KEY) => false];
        }
        foreach ($params as $middleware) {
            if (!class_exists($middleware)) {
                throw new AppException("Middleware '$middleware' does not exit", 500);
            }
            $result = $app->make($middleware)->run();
            if (isset($result['status'])) {
                if (!empty($result['status'])) {
                    $app->replace(Request::class, function () use ($result) {
                        return $result['request'];
                    });
                    continue;
                }
                return Response::json($result, $result['code']);
            }
            return $result;
        }
        return [concat('', 'passable', PROJECT_KEY) => true];
    }

    public function resolveRequiredMiddleware($params)
    {
        $kernel = app(Kernel::class);
        foreach ($kernel->getRequireMiddleware() as $middleware) {
            $params[] = $middleware;
        }
        return $params;
    }
}