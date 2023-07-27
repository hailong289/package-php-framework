<?php
namespace Core;

class BaseController {
    public function model($model){
        if(file_exists($model.'.php')){
            require_once $model.'.php';
            if(class_exists($model)){
                $model = new $model();
                return $model;
            }
        }
        return false;
    }
    // Render ra view
    public function render_view($views, $data = [])
    {
        // Đổi key mảng thành biến
        extract($data);
        $views = preg_replace('/([.]+)/', '/' , $views);
        if(file_exists(__DIR__ROOT . '/app/views/'.$views.'.view.php')){
            require_once __DIR__ROOT . '/app/views/'.$views.'.view.php';
        }
    }
}