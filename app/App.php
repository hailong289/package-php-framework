<?php
namespace App;
use App\Controllers\HomeController;
use Core\Router;

class App {
    private $controller;
    private $action;
    private $param;
    private $router;
    public function __construct()
    {
        $this->router = new Router();
        $url = $this->router->handle($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
        $this->handleUrl($url);
    }
    public function handleUrl($url){
        try {
            $urlarr = array_values($url);
            if (!empty($urlarr[0])) {
                $this->__controller = $urlarr[0];
                // Kiểm tra file có tồn tại
                if (file_exists($urlarr[0] . '.php')) {
                    require_once $urlarr[0] . '.php';
                    // Kiểm tra class tồn tại
                    if (class_exists($this->__controller)) {
                        $this->__controller = new $this->__controller();
                    } else {
                        die('page not found');
                    }
                    //   xóa phần tử khi thực hiện xong
                    unset($urlarr[0]);
                } else {
                    throw new \Exception('page not found');
                }
            } else {
                throw new \Exception('page not found');
            }
            // xử lý action
            if (isset($urlarr[1])) {
                $this->__action = $urlarr[1];
                //   xóa phần tử khi thực hiện xong
                unset($urlarr[1]);
            }
            if (method_exists($this->__controller, $this->__action)) {
                // xử lý method
                $this->__controller->{$this->__action}();
            }else{
                throw new \Exception('Method does not exit');
            }
        }catch (\Exception $e){
            die($e->getMessage());
        }
    }
}