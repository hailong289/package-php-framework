<?php
namespace App\Core;

use App\Middleware\Kernel;

class Router {
    protected static $method = 'GET';
    protected static $action;
    protected static $path;
    protected static $routers;
    private static $name_middleware = '';
    private static $prefix = '';

    public function __construct($name = null, $is_prefix = false){
        if($name) {
            if ($is_prefix) {
                self::$prefix = (preg_match('/^\//', $name) ? $name: '/'.$name);
            } else {
                self::$name_middleware = $name;
            }
        }
    }

    public function handle($url, $method){
        $routers = self::$routers;
        $action = '';
        $path = '';
        $current_router = [];
        $method = $_REQUEST['_method'] ?? $method;
        foreach ($routers as $router){
            $path_router = $router['path'];
            $method_router = $router['method'];
            $action_router = $router['action'];
            $check_has_params = preg_match('/\d+/', $url);
            $check_query_string = preg_match('/(&|\?)([a-z_]+)=([a-z0-9]+)/i', $url);
            $check_router_param = preg_match('/{([a-z]+)}/',$path_router);
            if ((!$check_has_params || $check_query_string) && !$check_router_param) {
                $path_arr = array_values(array_filter(explode('/',$path_router)));
                $url_arr = array_filter(explode('/', $url));
                if(
                    count($path_arr) === count($url_arr) &&
                    strcmp(strtok($url,'?'), $path_router) === 0 &&
                    $method_router === $method
                ){
                    if ($path === $path_router) {
                        throw new \RuntimeException("Duplicate router", 500);
                    }
                    $action = $action_router;
                    $path = $path_router;
                    $current_router = $router;
                }

            } else if ($check_has_params && $check_router_param && !$check_query_string){ // check router with params
                $path_arr = array_values(array_filter(explode('/',$path_router)));
                $url_arr = array_values(array_filter(explode('/', $url)));
                if(count($path_arr) === count($url_arr)){
                    preg_match_all('/{([a-z]+)}/', $path_router, $params_array);
                    $result = array_diff($path_arr, $url_arr);
                    if(count($result) === count($params_array[0]) && $method_router === $method){
                        if ($path === $path_router) {
                            throw new \RuntimeException("Duplicate router", 500);
                        }
                        // $result sẽ là params
                        $params = array_diff($url_arr, $path_arr);
                        $action = array_merge($action_router, $params);
                        $path = $path_router;
                        $current_router = $router;
                    }
                }
            }
        }

        if(empty($action) || count($current_router) == 0) throw new \RuntimeException("Not found", 404);
        $names = $current_router['middleware'];
        if($names && is_string($names)) {
            $result = $this->middlewareWork($names);
            if($result->error_code){
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($result);
                exit();
            }
        }elseif ($names && is_array($names)){
            $is_error = false;
            $errors_return = [];
            foreach ($names as $name){
                $result = $this->middlewareWork($name);
                if($result->error_code){
                    $errors_return[] = $result;
                    $is_error = true;
                }
            }
            usort($errors_return, function ($item1, $item2) {
                return isset($item1->middleware_not_exist) ? -1:1;
            });
            if($is_error && count($errors_return)){
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($errors_return[0]);
                exit();
            }
        }
        return $action;
    }

    public static function get($name,  $action): void
    {
        self::$method = 'GET';
        self::$path = self::$prefix . (preg_match('/^\//', $name) ? $name: '/'.$name);
        self::$action = $action;
        self::$routers[] = [
            'method' => self::$method,
            'path' => self::$prefix && endsWith(self::$path, '/') ? substr(self::$path, 0, -1):self::$path,
            'action' => self::$action,
            'middleware' => self::$name_middleware ?? null
        ];
    }
    public static function post($name, $action): void{
        self::$method = 'POST';
        self::$path = self::$prefix . (preg_match('/^\//', $name) ? $name: '/'.$name);
        self::$action = $action;
        self::$routers[] =[
            'method' => self::$method,
            'path' => self::$prefix && endsWith(self::$path, '/') ? substr(self::$path, 0, -1):self::$path,
            'action' => self::$action,
            'middleware' => self::$name_middleware ?? null
        ];
    }
    public static function put($name, $action): void{
        self::$method = 'PUT';
        self::$path = self::$prefix . (preg_match('/^\//', $name) ? $name: '/'.$name);
        self::$action = $action;
        self::$routers[] =[
            'method' => self::$method,
            'path' => self::$prefix && endsWith(self::$path, '/') ? substr(self::$path, 0, -1):self::$path,
            'action' => self::$action,
            'middleware' => self::$name_middleware ?? null
        ];
    }
    public static function patch($name, $action): void{
        self::$method = 'PATH';
        self::$path = self::$prefix . (preg_match('/^\//', $name) ? $name: '/'.$name);
        self::$action = $action;
        self::$routers[] =[
            'method' => self::$method,
            'path' => self::$prefix && endsWith(self::$path, '/') ? substr(self::$path, 0, -1):self::$path,
            'action' => self::$action,
            'middleware' => self::$name_middleware ?? null
        ];
    }
    public static function delete($name, $action): void{
        self::$method = 'DELETE';
        self::$path = self::$prefix . (preg_match('/^\//', $name) ? $name: '/'.$name);
        self::$action = $action;
        self::$routers[] =[
            'method' => self::$method,
            'path' => self::$prefix && endsWith(self::$path, '/') ? substr(self::$path, 0, -1):self::$path,
            'action' => self::$action,
            'middleware' => self::$name_middleware ?? null
        ];
    }
    public static function head($name, $action): void{
        self::$method = 'HEAD';
        self::$path = self::$prefix . (preg_match('/^\//', $name) ? $name: '/'.$name);
        self::$action = $action;
        self::$routers[] =[
            'method' => self::$method,
            'path' => self::$prefix && endsWith(self::$path, '/') ? substr(self::$path, 0, -1):self::$path,
            'action' => self::$action,
            'middleware' => self::$name_middleware ?? null
        ];
    }
    public static function options($name, $action): void{
        self::$method = 'OPTIONS';
        self::$path = self::$prefix . (preg_match('/^\//', $name) ? $name: '/'.$name);
        self::$action = $action;
        self::$routers[] =[
            'method' => self::$method,
            'path' => self::$prefix && endsWith(self::$path, '/') ? substr(self::$path, 0, -1):self::$path,
            'action' => self::$action,
            'middleware' => self::$name_middleware ?? null
        ];
    }

    public static function middleware($name){
        return new static($name);
    }

    public static function prefix($path){
        return new static($path, true);
    }

    public static function group($function)
    {
        $function();
        self::clear();
    }

    public static function clear(): void{
        if(self::$prefix){
            self::$prefix = '';
        }else{
            if(self::$name_middleware) self::$name_middleware = '';
        }
    }

    public function middlewareWork($names){
        require_once 'middleware/Kernel.php';
        $kernel = new Kernel();
        if(!empty($kernel->routerMiddleware[$names])){
            $class = $kernel->routerMiddleware[$names];
            $path_middleware = __DIR__ROOT . '/middleware/'. $names. 'Middleware';
            if(file_exists($path_middleware.'.php')) {
                require_once $path_middleware . '.php';
                $call_middleware = new $class();
                $handler = $call_middleware->handle(new Request());
                $return_middleware = is_array($handler) ? (object)$handler:(is_bool($handler) ? $handler:$handler);
                return $return_middleware;
            }
        }else{
            return (object)[
                'error_code' => 1,
                'message' => "Middleware $names does not exist",
                'middleware_not_exist' => 1
            ];
        }
    }
}