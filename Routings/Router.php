<?php
namespace Hola\Routings;

class Router {
    private static BuildRouter|null $instance = null;
    public function url() {
        return $this->handle($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
    }

    public static function build() {
        if (is_null(self::$instance)) {
            self::$instance = new BuildRouter();
        }
        return self::$instance;
    }

    public static function get($uri, $actions)
    {
        return self::build()->get($uri, $actions);
    }

    public static function post($uri, $actions)
    {
        return self::build()->post($uri, $actions);
    }

    public static function put($uri, $actions)
    {
        return self::build()->put($uri, $actions);
    }

    public static function patch($uri, $actions)
    {
        return self::build()->patch($uri, $actions);
    }

    public static function delete($uri, $actions)
    {
        return self::build()->delete($uri, $actions);
    }

    public static function options($uri, $actions)
    {
        return self::build()->options($uri, $actions);
    }

    public static function head($uri, $actions)
    {
        return self::build()->head($uri, $actions);
    }

    public static function group($callback)
    {
        return self::build()->group($callback);
    }

    public static function middleware($middleware)
    {
        return self::build()->middleware($middleware);
    }

    public static function prefix($prefix)
    {
        return self::build()->prefix($prefix);
    }

    public static function list()
    {
        return self::build()->list();
    }

    public static function mainPath($name) {
        return self::build()->mainPath($name);
    }

    private function handle($url, $method) {
        $requestMethod = $method;
        $requestUri = rtrim($url, '/') ?: '/';
        $routers = self::list();
        $result = [];
        $matches = [];
        foreach($routers as $route) {
            if (self::match($route, $requestMethod, $requestUri, $matches)) {
                if (count($matches) > 0) {
                    array_shift($matches);
                }
                $result = [
                    'action' => array_merge($route['callback'], $matches),
                    'middlewares' => $route['middlewares']
                ];
                break;
            }
        }
        if (empty($result)){
            throw new \Exception('Router not found', 404);
        }
        return $result;
    }

    private function match(array $route, string $method, string $uri, array &$matches): bool {
        if ($route['method'] !== $method) {
            return false;
        }
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route['path']);
        return preg_match("#^$pattern$#", $uri, $matches);
    }
}