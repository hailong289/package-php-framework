<?php

namespace Hola\Core;

use Hola\Data\Collection;
use Hola\Data\ShareData;

class Response {

    public static function redirectTo($path, $status = 302, $headers = []){
        header('Location: ' . $path, true, $status);
        exit();
    }
    
    public static function json($data = [], $status = 200, $headers = []){
        $headers['Content-Type'] = 'application/json; charset=utf-8';
        self::setHeaders($headers, $status);
        self::resloveDataCollect($data);
        ShareData::init()->create('data', $data);
        return $data;
    }

    public static function view($view, $data = [], $headers = [], $status = 200, $callback = null){
        $headers['Content-Type'] = 'text/html; charset=utf-8';
        self::setHeaders($headers, $status);
        self::resloveDataCollect($data);
        ShareData::init()->create('data', $data);
        return ViewRender::render($view, $data);
    }

    public static function xmlFromData($data = [], $status = 200, $headers = [])
    {
        $headers['Content-Type'] = 'application/xml; charset=utf-8';
        self::setHeaders($headers, $status);
        return ViewRender::renderXml($data);
    }

    public static function withExit($type, \Closure $callback){
        $data = $callback();
        if ($type === 'json') {
            echo self::json(...$data);
        } else if ($type === 'view') {
            self::view(...$data);
        } else if ($type === 'require_once') {
            require_once($data);
        } else if ($type === 'include') {
            include($data);
        } else if ($type === 'readfile') {
            readfile($data);
        } else {
            echo $data;
        }
        exit();
    }

    private static function setHeaders($headers = [], $status = 200)
    {
        foreach($headers as $key => $value){
            if (is_numeric($key)) {
                header($value, true, $status);
            } else {
                header($key . ': ' . $value, true, $status);
            }
        }
    }

    public function next(Request $request, $code = 0){
        if ($code !== 0) http_response_code($code);
        $data = [
            "pass_middleware" => 1, 
            'request' => $request
        ];
        return $data;
    }

    public function close($string = '', $code = 0){
        if ($code !== 0) http_response_code($code);
        return ["message" => $string];
    }

    private static function resloveDataCollect(&$data){
        if ($data instanceof Collection) {
            $data = $data->data;
            return new self();
        }
        foreach ($data as $key => $value) {
            if ($value instanceof Collection) {
                $data[$key] = $value->data;
            } else if (is_array($value)) {
                self::resloveDataCollect($value);
            }
        }
        return new self();
    }
}