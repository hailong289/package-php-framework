<?php

namespace Hola\Core;
use Hola\Container\Container;
use Hola\Exceptions\AppException;
use Hola\Transport\Request;
use Hola\Transport\Response;
use App\Middleware\Kernel;

abstract class Middleware {
    private $bindings = [];

    abstract public function handle(Request $request, Response $response);

    public function run()
    {
        try {
            return $this->handle(app(Request::class), app(Response::class));
        } catch (\Throwable $e) {
            if (method_exists($this, 'failed')) {
                return $this->failed($e);
            }
            return [
                "pass_middleware" => false,
                "message" => $e->getMessage(),
                "code" => $e->getCode(),
                "file" => $e->getFile(),
                "line" => $e->getLine(),
                "trace" => $e->getTraceAsString(),
                "previous" => $e->getPrevious()
            ];
        }
    }
}