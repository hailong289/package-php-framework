<?php
namespace Hola\Transport;

class Response {
    private static ResponseBuilder|null $instance = null;

    public function __construct() {}

    public static function build(): ResponseBuilder {
        if (is_null(self::$instance)) {
            self::$instance = new ResponseBuilder();
        }
        return self::$instance;
    }

    public static function json($data = [], $status = 200, $headers = []){
        return self::build()->json($data, $status, $headers);
    }

    public static function view($view, $data = [], $status = 200, $headers = []){
        return self::build()->view($view, $data, $status, $headers);
    }

    public static function redirect($url, $status = 302, $headers = []){
        return self::build()->redirectTo($url, $status, $headers);
    }

    public static function xml($data = [], $status = 200, $headers = []){
        return self::build()->xmlFromData($data, $status, $headers);
    }

    public static function withExit($type, \Closure $callback){
        return self::build()->withExit($type, $callback);
    }

    public static function next($request){
        return self::build()->next($request);
    }

    public static function close($string = '', $code = 200){
        return self::build()->close($string, $code);
    }

}