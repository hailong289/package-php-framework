<?php

namespace App\Core;

class Response {
    public static function redirectTo($path, $status = 302, $headers = []){
        header('Location: ' . $path, true, $status);
        exit();
    }
    public static function json($data = [], $status = 200, $headers = [], $options = 0){
        if(is_array($data) || is_object($data)) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
            return $data;
        }
        throw new \RuntimeException('Data is not an array or object');
    }

    public static function view($view, $data = [], $status = 200){
        http_response_code($status);
        if(count($data)) $GLOBALS['share_date_view'] = $data;
        extract($data);
        $views = preg_replace('/([.]+)/', '/' , $view);
        require_once __DIR__ROOT . '/app/views/'.$views.'.view.php';
        return true;
    }
}