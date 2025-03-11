<?php

namespace Hola\Transport;
use Hola\Container\Container;
use Hola\Exceptions\AppException;

class Middleware {
    private $bindings = [];

    public function run()
    {
        try {
            if (!method_exists($this, 'handle')) {
                if ($this instanceof \App\Http\Middleware\VerifyCsrfToken) {
                    return $this->resloveVerifyCsrfToken();
                }
                throw new AppException("Method 'handle' does not exit", 500);
            }
            return $this->handle(app(Request::class), app(Response::class));
        } catch (\Throwable $e) {
            if (method_exists($this, 'failed')) {
                return $this->failed($e);
            }
            return [
                "status" => false,
                "message" => $e->getMessage(),
                "code" => $e->getCode() ? $e->getCode() : 500,
                "file" => $e->getFile(),
                "line" => $e->getLine(),
                "trace" => $e->getTraceAsString(),
                "previous" => $e->getPrevious()
            ];
        }
    }

    public function resloveVerifyCsrfToken()
    {
        $request = app(Request::class);
        if ($request->isGet()) {
            return Response::next($request);
        }
        /* Do not check CSRF token with these paths */
        if (!empty($this->except) && $this->exceptPaths($request->path(), $this->except)) {
            return Response::next($request);
        }

        $crsfToken = $request->headers('X-CSRF-TOKEN') || $request->csrf_token;
        if (empty($crsfToken)) {
            if ($request->isJson()) {
                return Response::json([
                    "message" => "CSRF token not found",
                    "code" => 500
                ], 500);
            }
            return Response::view('error.index', [
                "message" => "CSRF token not found",
                "code" => 500
            ], 500);
        }

        if ($crsfToken !== $request->session('csrf_token')) {
            if ($request->isJson()) {
                return Response::json([
                    "message" => "CSRF token not match",
                    "code" => 401
                ], 401);
            }
            return Response::view('error.index', [
                "message" => "CSRF token not match",
                "code" => 401
            ], 401);
        }

        return Response::next($request);
    }

    public function exceptPaths($path, $except) {
        foreach ($except as $pattern) {
            if ($pattern === '*') {
                return true;
            }
            $pattern = str_replace('\*', '.*', preg_quote($pattern, '/'));
            if (preg_match("#^$pattern$#i", $path)) {
                return true;
            }
        }
        return false;
    }

}