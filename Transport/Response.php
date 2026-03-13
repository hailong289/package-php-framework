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
        return self::build()->json($data);
    }

    public static function view($view, $data = []){
        return self::build()->view($view, $data);
    }

    public static function redirect($url){
        return self::build()->redirect($url);
    }

    public static function xml($data = []){
        return self::build()->xml($data);
    }

    public static function terminate() {
        return self::build()->terminate();
    }
    
    public static function setStatus($code)
    {
        return self::build()->setStatus($code);
    }

    public static function setHeaders(array $headers) {
        return self::build()->setHeaders($headers);
    }

    public static function metaTag(array $data = []) {
        return self::build()->metaTag($data);
    }
    
    public static function file($path)
    {
        return self::build()->file($path);
    }
    
    public static function download($path)
    {
        return self::build()->download($path);
    }

    public static function text($string)
    {
        return self::build()->text($string);
    }
    
    public static function noContent()
    {
        return self::build()->noContent();
    }
}