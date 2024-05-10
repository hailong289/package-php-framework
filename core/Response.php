<?php

namespace System\Core;

class Response {
    public static function redirectTo($path, $status = 302, $headers = []){
        header('Location: ' . $path, true, $status);
        exit();
    }
    public static function json($data = [], $status = 200, $headers = [], $options = 0){
        http_response_code($status);
        return $data;
    }

    public static function view($view, $data = [], $status = 200){
        if(count($data)) $GLOBALS['share_data_view'] = $data;
        extract($data);
        $views = preg_replace('/([.]+)/', '/' , $view);
        if(!file_exists(__DIR__ROOT . '/App/Views/'.$views.'.view.php') && $view === 'error.index') {
            require_once 'view/error.view.php';
            return;
        }
        if(!file_exists(__DIR__ROOT . '/App/Views/'.$views.'.view.php')){
            throw new \RuntimeException("File App/Views/$view.view.php does not exist", 500);
        }
        http_response_code($status);
        $file = __DIR__ROOT . '/App/Views/'.$views.'.view.php';
        require_once $file;
        return $file;
    }
}