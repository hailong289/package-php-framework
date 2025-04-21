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
                return Response::json(["message" => "CSRF token not found"])->setStatus(419);
            }
            return Response::view('error.index', ["message" => "CSRF token not found"])->setStatus(419);
        }

        if (!hash_equals($request->session('csrf_token'), $crsfToken)) {
            if ($request->isJson()) {
                return Response::json(["message" => "CSRF token not match"])->setStatus(403);
            }
            return Response::view('error.index', ["message" => "CSRF token not match"])->setStatus(403);
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