<?php

namespace Hola\Transport;
use App\Http\Middleware\Kernel;
use Hola\Application;
use Hola\Exceptions\AppException;

class MiddlewareBuilder {

    public function handle($callback) {
        /** @var Application $app */
        [$params, $app] = $callback();
        $params = $this->resolveRequiredMiddleware($params);
        if (empty($params)) {
            return [concat('', 'passable', PROJECT_KEY) => false];
        }
        $key = concat('', 'passable', PROJECT_KEY);
        foreach ($params as $middleware) {
            if (!class_exists($middleware)) {
                throw new AppException("Middleware '$middleware' does not exit", 500);
            }
            $result = $app->make($middleware)->run();
            if ($result instanceof ResponseBuilder) {
                $data = $result->callback();
                if (!empty($data[concat('', 'passable', PROJECT_KEY)])) {
                    $app->replace(Request::class, function () use ($data) {
                        return $data['request'];
                    });
                    continue;
                }
                return [$key => false, 'return' => $result];
            } else if (is_bool($result)) {
                if (!$result) {
                    $data = [
                        "message" => "Middleware $middleware not passable",
                        "code" => 403
                    ];
                    if ($app->make(Request::class)->isJson()) {
                        return [
                            $key => false,
                            'return' => Response::json($data)->setStatus(403)
                        ];
                    }
                    return [
                        $key => false,
                        'return' => Response::view("error.index", $data)->setStatus(403)
                    ];
                }
                continue;
            }
            return [$key => false, 'return' => $result];
        }
        return [$key => true];
    }

    public function resolveRequiredMiddleware($params)
    {
        $kernel = app(Kernel::class);
        foreach ($kernel->getRequiredMiddleWares() as $middleware) {
            $params[] = $middleware;
        }
        return $params;
    }

}