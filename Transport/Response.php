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

    public static function json($data = []){
        return self::build()->json($data, $status, $headers);
    }

    public static function view($view, $data = []){
        return self::build()->view($view, $data, $status, $headers);
    }

    public static function redirect($url){
        return self::build()->redirectTo($url, $status, $headers);
    }

    public static function xml($data = []){
        return self::build()->xmlFromData($data, $status, $headers);
    }

    public static function exit() {
        return self::build()->exit();
    }

    public static function next($request){
        return self::build()->next($request);
    }

    public static function close($string = ''){
        return self::build()->close($string);
    }

    public static function setStatus($code)
    {
        return self::build()->setStatus($code);
    }

    public static function setHeaders(array $headers) {
        return self::build()->setHeaders($headers);
    }

}