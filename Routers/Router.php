<?php
namespace Hola\Routers;

class Router extends BaseRouter {
    public function url() {
        return $this->handle($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
    }

    private function handle($url, $method) {
        $requestMethod = $method;
        $requestUri = rtrim($url, '/') ?: '/';
        $routers = self::list();
        $result = [];
        foreach($routers as $route) {
            if (self::match($route, $requestMethod, $requestUri)) {
                $result = [
                    'action' => $route['callback'],
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

    private function match(array $route, string $method, string $uri): bool {
        if ($route['method'] !== $method) {
            return false;
        }
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route['path']);
        return preg_match("#^$pattern$#", $uri);
    }
}