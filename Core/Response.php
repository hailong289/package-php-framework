<?php

namespace Hola\Core;

use Hola\Data\Collection;

class Response {
    public static function redirectTo($path, $status = 302, $headers = []){
        header('Location: ' . $path, true, $status);
        exit();
    }
    
    public static function json($data = [], $status = 200, $headers = [], $options = 0){
        $headers['Content-Type'] = 'application/json; charset=utf-8';
        self::setHeaders($headers, $status);
        self::resloveDataCollect($data);
        return $data;
    }

    public static function view($view, $data = [], $headers = [], $status = 200){
        self::resloveDataCollect($data);
        $folder_view = __DIR__ROOT . '/App/Views/';
        $headers['Content-Type'] = 'text/html; charset=utf-8';
        self::setHeaders($headers, $status);
        $views = preg_replace('/([.]+)/', '/' , $view);
        $file_view = "{$folder_view}{$views}.view.php";
        if(!file_exists($file_view)){
            if ($view === 'error.index') {
                $path = dirname(__DIR__, 1);
                $file_view = "$path/view/error.view.php";
                require_once $file_view;
                return $file_view;
            }
            throw new \RuntimeException("File App/Views/$view.view.php does not exist", 500);
        }
        $GLOBALS['share_data_view'] = $data;
        return self::renderView($file_view);
    }

    private static function renderView($file_view)
    {
        if(!empty($GLOBALS['share_data_view'])) {
            extract($GLOBALS['share_data_view']);
        }
        $folder = __DIR__ROOT . '/storage/render';
        $startPos = strpos($file_view, 'Views');
        $view = substr($file_view, $startPos);
        $view_render = "$folder/$view";
        $view_render = str_replace('.view.php', '.php', $view_render);
        if (file_exists($view_render)) {
            require_once $view_render;
            return $view_render;
        }
        createFolder(getFolder($view_render));
        $content = ViewRender::render($file_view);
        file_put_contents($view_render, $content);
        require_once $view_render;
        return $view_render;
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