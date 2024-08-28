<?php

namespace Hola\Core;

class Response {
    public static function redirectTo($path, $status = 302, $headers = []){
        header('Location: ' . $path, true, $status);
        exit();
    }
    
    public static function json($data = [], $status = 200, $headers = [], $options = 0){
        $headers['Content-Type'] = 'application/json; charset=utf-8';
        self::setHeaders($headers, $status);
        return $data;
    }

    public static function view($view, $data = [], $headers = [], $status = 200){
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
        return self::renderView($file_view, $data);
    }

    private static function renderView($file, $data = [])
    {
        if(count($data)) {
            $GLOBALS['share_data_view'] = $data;
            extract($data);
        }
        $folder = __DIR__ROOT . '/storage/render/views';
        $content = file_get_contents($file);
        $content = preg_replace('/\$php\s(.?)\s\$endphp/', '<?php $1 ?>', $content);
        $content = preg_replace([
            '/\$foreach\((.*?)\)/s',
            '/\$endforeach/'
        ], [
            '<?php foreach($1): ?>',
            '<?php endforeach; ?>'
        ], $content);
        createFolder($folder);
        $view = end(explode("/", $file));
        $file_name = "$folder/$view";
        file_put_contents($file_name, $content);
        require_once $file_name;
        return $file_name;
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