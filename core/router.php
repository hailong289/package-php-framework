<?php
namespace Core;

class Router {
    protected static $method = 'GET';
    protected static $action;
    protected static $path;
    protected static $routers;

    public function __construct(){

    }

    public function handle($url, $method){

//        if(!isset(self::$routers[$method])){
//            die('Method not allowed');
//        }
        $routers = self::$routers;
//      if(strcmp($url, self::$path) !== 0){
//          die('Not found');
//      }
//        $with_ids = array_values(preg_grep( '/{([a-z]+)}/', array_keys(self::$routers[$method])));
//        $url_with_params = array_values(preg_grep( '//', array_keys(self::$routers[$method])));
        $action = '';
        $path = '';
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
                    }
                }
            }
        }
        if(empty($action)) throw new \Exception("Router not exist", 500);
        return $action;
    }

    public static function get($name,  $action){
        self::$method = 'GET';
        self::$path = $name;
        self::$action = $action;
        self::$routers[] =[
            'method' => self::$method,
            'path' => self::$path,
            'action' => self::$action
        ];
    }
    public static function post($name, $action){
        self::$method = 'POST';
        self::$path = $name;
        self::$action = $action;
        self::$routers[] =[
            'method' => self::$method,
            'path' => self::$path,
            'action' => self::$action
        ];
    }
    public static function put($name, $action){
        self::$method = 'PUT';
        self::$path = $name;
        self::$action = $action;
        self::$routers[] =[
            'method' => self::$method,
            'path' => self::$path,
            'action' => self::$action
        ];
    }
    public static function patch($name, $action){
        self::$method = 'PATH';
        self::$path = $name;
        self::$action = $action;
        self::$routers[] =[
            'method' => self::$method,
            'path' => self::$path,
            'action' => self::$action
        ];
    }
    public static function delete($name, $action){
        self::$method = 'DELETE';
        self::$path = $name;
        self::$action = $action;
        self::$routers[] =[
            'method' => self::$method,
            'path' => self::$path,
            'action' => self::$action
        ];
    }
    public static function head($name, $action){
        self::$method = 'HEAD';
        self::$path = $name;
        self::$action = $action;
        self::$routers[] =[
            'method' => self::$method,
            'path' => self::$path,
            'action' => self::$action
        ];
    }
    public static function options($name, $action){
        self::$method = 'OPTIONS';
        self::$path = $name;
        self::$action = $action;
        self::$routers[] =[
            'method' => self::$method,
            'path' => self::$path,
            'action' => self::$action
        ];
    }
}