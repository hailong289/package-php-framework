<?php
namespace Core;

class Router {
    protected static $method = 'GET';
    protected static $action;
    protected static $path;
    protected static $routers;
    protected $is_access;

    public function __construct(){

    }

    public function handle($url, $method){

        if(!isset(self::$routers[$method])){
            die('Method not allowed');
        }
//      if(strcmp($url, self::$path) !== 0){
//          die('Not found');
//      }
        $key = array_values(preg_grep( '/{([a-z]+)}/', array_keys(self::$routers[$method])));

        var_dump($key);
        if(!isset(self::$routers[$method][$url])){

        }

        return self::$action;
    }

    public static function get($name,  $action){
        self::$method = 'GET';
        self::$path = $name;
        self::$action = $action;
        self::$routers[self::$method][self::$path] = self::$action;
    }
    public static function post($name, $action){
        self::$method = 'POST';
        self::$path = $name;
        self::$action = $action;
        self::$routers[self::$method][self::$path] = self::$action;
    }
    public static function put($name, $action){
        self::$method = 'PUT';
        self::$path = $name;
        self::$action = $action;
        self::$routers[self::$method][self::$path] = self::$action;
    }
    public static function patch($name, $action){
        self::$method = 'PATH';
        self::$path = $name;
        self::$action = $action;
        self::$routers[self::$method][self::$path] = self::$action;
    }
    public static function delete($name, $action){
        self::$method = 'DELETE';
        self::$path = $name;
        self::$action = $action;
        self::$routers[self::$method][self::$path] = self::$action;
    }
    public static function head($name, $action){
        self::$method = 'HEAD';
        self::$path = $name;
        self::$action = $action;
        self::$routers[self::$method][self::$path] = self::$action;
    }
    public static function options($name, $action){
        self::$method = 'OPTIONS';
        self::$path = $name;
        self::$action = $action;
        self::$routers[self::$method][self::$path] = self::$action;
    }
}