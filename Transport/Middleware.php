<?php

namespace Hola\Transport;
use Hola\Container\Container;
use Hola\Exceptions\AppException;

class Middleware {

    public function run(Request $request, \Closure $continue)
    {
        if ($this instanceof \App\Http\Middleware\CorsMiddleware) {
            return $this->resolveCors($request, $continue);
        } else if ($this instanceof \App\Http\Middleware\VerifyCsrfToken) {
            return $this->resolveVerifyCsrfToken($request, $continue);
        } 
        return $this->forward($request, $continue);
    }

    private function resolveVerifyCsrfToken(Request $request, \Closure $continue)
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

    private function exceptPaths($path, $except) {
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

    private function resolveCors(Request $request, $continue)
    {
        $origin = $request->originalDomain();

        if (!$origin || !$this->isAllowedOrigin($origin)) {
            return $continue($request);
        }

        $cors = [
            'Access-Control-Allow-Origin' => ($this->config['allowed_origins'][0] === '*' ? '*' : $origin),
            'Access-Control-Allow-Methods' => $this->implodeOrWildcard($this->config['allowed_methods']),
            'Access-Control-Allow-Headers' => $this->implodeOrWildcard($this->config['allowed_headers']),
            'Access-Control-Max-Age' => $this->config['max_age'],
        ];

        if (!empty($this->config['exposed_headers'])) {
            $cors['Access-Control-Expose-Headers'] = implode(', ', $this->config['exposed_headers']);
        }
        
        if ($this->config['supports_credentials']) {
            $cors['Access-Control-Allow-Credentials'] = 'true';
        }
        app()->response()->setHeaders($cors);
        return $continue($request);
    }

    private function isAllowedOrigin(string $origin): bool
    {
        if (empty($this->config['allowed_origins'])) {
            return true;
        }
        
        if (in_array('*', $this->config['allowed_origins'])) {
            return true;
        }

        return in_array($origin, $this->config['allowed_origins']);
    }

    private function implodeOrWildcard(array $list): string
    {
        return in_array('*', $list) ? '*' : implode(', ', $list);
    }

}