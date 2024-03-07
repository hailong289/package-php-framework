<?php
namespace System\Core;

class BaseController extends \stdClass {
    public function model($names) {
        $result = [];
        if (is_array($names)) {
            foreach ($names as $name){
                $variable = str_replace('App\\Models\\','', $name);
                $model = $name;
                if(file_exists(path_root($model.'.php'))){
                    require_once path_root($model.'.php');
                    if(class_exists($model)){
                        $model = new $model();
                        $this->{$variable} = $model;
                        return $model;
                    }else{
                        throw new \RuntimeException("Model $name does not exits", 500);
                    }
                }
            }
        }else{
            $model = $names;
            if(file_exists(path_root($model.'.php'))){
                require_once path_root($model.'.php');
                if(class_exists($model)){
                    return new $model();
                } else {
                    throw new \RuntimeException("Model $names does not exits", 500);
                }
            }
        }
    }
    // Render ra view
    public function render_view($views, $data = [])
    {
        // Đổi key mảng thành biến
        if(count($data)) $GLOBALS['share_date_view'] = $data;
        extract($data);
        $views = preg_replace('/([.]+)/', '/' , $views);
        if(file_exists(__DIR__ROOT . '/App/Views/'.$views.'.view.php')){
            require_once __DIR__ROOT . '/App/Views/'.$views.'.view.php';
        }
        return $this;
    }

}