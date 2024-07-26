<?php
namespace Hola\Core;
class BaseController extends \stdClass {
    public function model($names) {
        $result = [];
        if (is_array($names)) {
            foreach ($names as $name){
                $variable = str_replace('App\\Models\\','', $name);
                $model = $name;
                if(class_exists($model)){
                    $model = new $model();
                    $this->{$variable} = new $model();
                    return $this->{$variable};
                }else{
                    throw new \RuntimeException("Model $name does not exits", 500);
                }
            }
        } else {
            $model = $names;
            if(class_exists($model)){
                $model = new $model();
                $this->{$variable} = new $model();
                return $this->{$variable};
            }else{
                throw new \RuntimeException("Model $model does not exits", 500);
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
        $kernel = new \Middleware\Kernel();
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