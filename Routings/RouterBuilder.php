<?php

namespace Hola\Routings;
use Hola\Exceptions\AppException;

class RouterBuilder {
    private array $router = [];
    private $prefix = '';
    private $middlewares = [];
    private $uid = 0;
    private $mainPath = '';

    public function add($method, $uri, $actions)
    {
        $this->uid = uid();
        $this->method = $method;
        $this->path = $this->mainPath . $this->prefix . $uri;
        $this->callback = $actions;
        $this->router[$this->uid] = [
            'method' => $this->method,
            'path' => $this->reslovePath($this->path),
            'callback' => $this->callback,
            'middlewares' => $this->middlewares
        ];
        return $this;
    }

    public function get($uri, $actions)
    {
        return $this->add('GET', $uri, $actions);
    }

    public function post($uri, $actions)
    {
        return $this->add('POST', $uri, $actions);
    }

    public function put($uri, $actions)
    {
        return $this->add('PUT', $uri, $actions);
    }

    public function patch($uri, $actions)
    {
        return $this->add('PATCH', $uri, $actions);
    }

    public function delete($uri, $actions)
    {
        return $this->add('DELETE', $uri, $actions);
    }

    public function options($uri, $actions)
    {
        return $this->add('OPTIONS', $uri, $actions);
    }

    public function head($uri, $actions)
    {
        return $this->add('HEAD', $uri, $actions);
    }

    public function group($callback)
    {
        $callback($this);
        $this->prefix = '';
        $this->middlewares = [];
    }

    public function prefix($prefix)
    {
        $this->prefix = $this->normalizePath($prefix);
        return $this;
    }

    public function middleware($middleware)
    {
        $middleware = $this->resloveMiddlware($middleware);
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $callerClass = $backtrace[1]['class'] ?? null;
        if ($this->uid != 0 && is_null($callerClass)) {
            $this->router[$this->uid]['middlewares'] = array_merge($this->router[$this->uid]['middlewares'], $middleware);
        } else {
            $this->middlewares = array_merge($this->middlewares, $middleware);
        }
        return $this;
    }

    public function list() {
        return $this->router;
    }

    public function mainPath($path) {
        $this->mainPath = $path;
        return $this;
    }

    private function normalizePath(string $path): string {
        $path = rtrim($path, '/') ?: '/';
        return $path;
    }

    private function reslovePath($path) {
        $path = preg_replace('/^\/?/', '/', $path);
        $path = preg_replace('#//+#', '/', $path);
        $path = rtrim($path, '/') ?: '/';
        return $path;
    }

    private function resloveMiddlware($middleware)
    {
        $middleArray = is_array($middleware) ? $middleware: [$middleware];
        $contract = app()->make(\App\Http\Middleware\Kernel::class);
        foreach ($middleArray as $k => &$item) {
            if (isset($contract->routerMiddleware[$item])) {
                $item = $contract->routerMiddleware[$item];
            }
        }
        return $middleArray;
    }
}