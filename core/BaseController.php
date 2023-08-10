<?php
namespace Core;

class BaseController {
    public function model($names) {
        $result = [];
        if (is_array($names)) {
            foreach ($names as $name){
                $variable = str_replace('App\\Models\\','', $name);
                $model = $name;
                if(file_exists($model.'.php')){
                    require_once $model.'.php';
                    if(class_exists($model)){
                        $model = new $model();
//                        {$variable} = $model;
                    }else{
                        $result[] = (object)[
                            'error_code' => 1,
                            'message' => "Model $name does not exits"
                        ];
                    }
                }
            }
            if (count($result) > 0) {
                if($result[0]->error_code){
                    echo json_encode($result[0]);
                    exit();
                }
            }
        }else{
            $model = $names;
            if(file_exists($model.'.php')){
                require_once $model.'.php';
                if(class_exists($model)){
                    return new $model();
                }else{
                    echo json_encode([
                        'error_code' => 1,
                        'message' => "Model $names does not exits"
                    ]);
                    exit();
                }
            }
        }
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