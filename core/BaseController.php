<?php
namespace System\Core;
use System\Middleware\Kernel;
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
                        if($model instanceof Model) {
                            $this->{$variable} = $model;
                        }
                        return $model instanceof Model ? $model:$model;
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
                    if($model instanceof Model) {
                        return new $model();
                    }
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

    public function middleware($name)
    {
        $kernel = new Kernel();
        try {
            if(!empty($kernel->routerMiddleware[$name])){
                $class = $kernel->routerMiddleware[$name];
                $call_middleware = new $class();
                $handler = $call_middleware->handle(new Request());
                $return_middleware = is_array($handler) ? (object)$handler:(is_bool($handler) ? $handler:$handler);
                if($return_middleware->error_code){
                    echo json_encode($return_middleware);
                    exit();
                }
            }else{
                echo json_encode([
                    'error_code' => 1,
                    'message' => "Middleware $name does not exist"
                ]);
                exit();
            }
        }catch (\Throwable $e) {
            echo json_encode([
                'error_code' => 1,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);
            exit();
        }
    }
}