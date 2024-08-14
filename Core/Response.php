<?php

namespace Hola\Core;

class Response {
    public static function redirectTo($path, $status = 302, $headers = []){
        header('Location: ' . $path, true, $status);
        exit();
    }
    
    public static function json($data = [], $status = 200, $headers = [], $options = 0){
        header('Accept: application/json; charset=utf-8', true, $status);
        self::setHeaders($headers);
        return $data;
    }

    public static function view($view, $data = [], $headers = [], $status = 200){
        header('Content-type: text/html; charset=utf-8', true, $status);
        self::setHeaders($headers);
        if(count($data)) $GLOBALS['share_data_view'] = $data;
        extract($data);
        $views = preg_replace('/([.]+)/', '/' , $view);
        if(!file_exists(__DIR__ROOT . '/App/Views/'.$views.'.view.php') && $view === 'error.index') {
            $file = 'view/error.view.php';
            require_once $file;
            return $file;
        }
        if(!file_exists(__DIR__ROOT . '/App/Views/'.$views.'.view.php')){
            throw new \RuntimeException("File App/Views/$view.view.php does not exist", 500);
        }
        $file = __DIR__ROOT . '/App/Views/'.$views.'.view.php';
        require_once $file;
        return $file;
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
}