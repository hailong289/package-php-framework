<?php

namespace Hola\Routers;

class BaseRouter {
    private static array $router = [];
    private static $prefix = '';
    private static $middlewares = [];

    public static function add($method, $uri, $callback)
    {
        $uri = self::$prefix . self::normalizePath($uri);
        self::$router[] = [
            'method' => $method,
            'path' => preg_replace('/^\/?/', '/', $uri),
            'callback' => $callback,
            'middlewares' => self::$middlewares
        ];
    }

    public static function get($uri, $callback)
    {
        self::add('GET', $uri, $callback);
        return new static();
    }

    public static function post($uri, $callback)
    {
        self::add('POST', $uri, $callback);
        return new static();
    }

    public static function put($uri, $callback)
    {
        self::add('PUT', $uri, $callback);
        return new static();
    }

    public static function delete($uri, $callback)
    {
        self::add('DELETE', $uri, $callback);
        return new static();
    }

    public static function patch($uri, $callback)
    {
        self::add('PATCH', $uri, $callback);
        return new static();
    }

    public static function group()
    {
        $fun_ags = func_get_args();
        if (count($fun_ags) < 1) {
            if ($fun_ags[0] instanceof \Closure) {
                $callback(new static());
                return;
            }
            throw new \Exception("Prefix is required");
        }
        $previousPrefix = self::$prefix;
        self::$prefix .= self::normalizePath($fun_ags[0]);
        $fun_ags[1](new Router());
        self::$prefix = $previousPrefix;
        self::$middlewares = [];
    }

    public static function middleware($middleware)
    {
        self::$middlewares[] = $middleware;
        return new Router();
    }

    private static function normalizePath(string $path): string {
        $path = rtrim($path, '/') ?: '/';
        return $path;
    }

    public static function list() {
        return self::$router;
    }
}