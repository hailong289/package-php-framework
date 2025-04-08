<?php

namespace Hola\Transport;
use Hola\Container\Container;
use Hola\Exceptions\AppException;

class Middleware {

    public function run(Request $request, \Closure $continue)
    {
        if ($this instanceof \App\Http\Middleware\VerifyCsrfToken) {
            return $this->resolveVerifyCsrfToken($request, $continue);
        }
        return $this->forward($request, $continue);
    }

    public function resolveVerifyCsrfToken(Request $request, \Closure $continue)
    {
        if (
            $request->isGet() ||
            (!empty($this->except) && $this->exceptPaths($request->path(), $this->except))
        ) {
            return $continue($request);
        }

        $crsfToken = $request->headers('X-CSRF-TOKEN', $request->csrf_token);
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
        
        $request->session()->remove('csrf_token');

        return $continue($request);
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