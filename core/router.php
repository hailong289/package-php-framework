<?php
namespace Core;

use App\Core\Middleware;
use App\Middleware\Kernel;

class Router {
    protected static $method = 'GET';
    protected static $action;
    protected static $path;
    protected static $routers;
    private static $name_middleware = [];

    public function __construct($middleware = null){
        if($middleware) self::$name_middleware = $middleware;
    }

    public function handle($url, $method){
        $routers = self::$routers;
        $action = '';
        $path = '';
        $current_router = [];
        foreach ($routers as $router){
            $path_router = $router['path'];
            $method_router = $router['method'];
            $action_router = $router['action'];
            $check_has_params = preg_match('/([0-9]+)/', $url);
            if (!$check_has_params && !preg_match('/{([a-z]+)}/',$path_router)) {
                $path_arr = array_values(array_filter(explode('/',$path_router)));
                $url_arr = array_filter(explode('/', $url));
                if(count($path_arr) == count($url_arr)){
                    if (strcmp($url, $path_router) === 0) {
                        if ($method_router != $method) {
                            throw new \Exception("Method router not match", 500);
                        }
                        if ($path === $path_router) {
                            throw new \Exception("Duplicate router", 500);
                        }

                        $action = $action_router;
                        $path = $path_router;
                        $current_router = $router;
                    }
                }

            } else if (preg_match('/{([a-z]+)}/',$path_router)){ // check router with params
                $path_arr = array_values(array_filter(explode('/',$path_router)));
                $url_arr = array_filter(explode('/', $url));
                if(count($path_arr) == count($url_arr)){
                    $result = array_diff($url_arr,$path_arr);
                    if(count($result) < count($url_arr)){
                        if($method_router != $method){
                            throw new \Exception("Method router not match", 500);
                        }
                        if($path === $path_router){
                            throw new \Exception("Duplicate router", 500);
                        }
                        // $result sẽ là params
                        $action = array_merge($action_router, $result);
                        $path = $path_router;
                        $current_router = $router;
                    }
                }
            }
        }
        if(empty($action) && count($current_router) == 0) throw new \Exception("Router not exist", 500);
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

    public static function get($name,  $action){
        self::$method = 'GET';
        self::$path = $name;
        self::$action = $action;
        self::$routers[] =[
            'method' => self::$method,
            'path' => self::$path,
            'action' => self::$action,
            'middleware' => self::$name_middleware ?? null
        ];
    }
    public static function post($name, $action){
        self::$method = 'POST';
        self::$path = $name;
        self::$action = $action;
        self::$routers[] =[
            'method' => self::$method,
            'path' => self::$path,
            'action' => self::$action,
            'middleware' => self::$name_middleware ?? null
        ];
    }
    public static function put($name, $action){
        self::$method = 'PUT';
        self::$path = $name;
        self::$action = $action;
        self::$routers[] =[
            'method' => self::$method,
            'path' => self::$path,
            'action' => self::$action,
            'middleware' => self::$name_middleware ?? null
        ];
    }
    public static function patch($name, $action){
        self::$method = 'PATH';
        self::$path = $name;
        self::$action = $action;
        self::$routers[] =[
            'method' => self::$method,
            'path' => self::$path,
            'action' => self::$action,
            'middleware' => self::$name_middleware ?? null
        ];
    }
    public static function delete($name, $action){
        self::$method = 'DELETE';
        self::$path = $name;
        self::$action = $action;
        self::$routers[] =[
            'method' => self::$method,
            'path' => self::$path,
            'action' => self::$action,
            'middleware' => self::$name_middleware ?? null
        ];
    }
    public static function head($name, $action){
        self::$method = 'HEAD';
        self::$path = $name;
        self::$action = $action;
        self::$routers[] =[
            'method' => self::$method,
            'path' => self::$path,
            'action' => self::$action,
            'middleware' => self::$name_middleware ?? null
        ];
    }
    public static function options($name, $action){
        self::$method = 'OPTIONS';
        self::$path = $name;
        self::$action = $action;
        self::$routers[] =[
            'method' => self::$method,
            'path' => self::$path,
            'action' => self::$action,
            'middleware' => self::$name_middleware ?? null
        ];
    }

    public static function middleware($name){
        return new static($name);
    }

    public static function group($function){
        $function();
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
                $handle = $call_middleware->handle(new Request());
                return $handle;
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