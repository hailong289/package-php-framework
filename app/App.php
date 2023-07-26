<?php
namespace App;
use App\Controllers\HomeController;
use Core\BaseController;
use Core\Request;
use Core\Router;

class App extends BaseController {
    private $controller;
    private $action;
    private $param = [];
    private $router;
    public function __construct()
    {
        $this->router = new Router();
        $this->handleUrl();
    }
    public function handleUrl(){
        try {
            $url = $this->router->handle($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
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
                         throw new \Exception('Page not found',404);
                    }
                    //   xóa phần tử khi thực hiện xong
                    unset($urlarr[0]);
                } else {
                    throw new \Exception('Page not found',404);
                }
            } else {
                throw new \Exception('Page not found', 404);
            }
            // xử lý action
            if (isset($urlarr[1])) {
                $this->__action = $urlarr[1];
                //   xóa phần tử khi thực hiện xong
                unset($urlarr[1]);
            }
            // xử lý param

            $this->__param = array_values($urlarr);
            if (method_exists($this->__controller, $this->__action)) {
                // xử lý method
                $method = new \ReflectionMethod($this->__controller, $this->__action);
                $agr = [];
                foreach ($method->getParameters() as $ag){
                    if($ag->name == 'request'){
                        array_push($agr, new Request());
                    }else{
                        $agr = array_merge($agr, $this->__param);
                    }
                }
                $this->__controller->{$this->__action}(...$agr);
            }else{
                throw new \Exception("Method {$this->__action} does not exit",400);
            }
        }catch (\Exception $e){
           ob_end_clean(); // remove echo
            $file = $e->getFile();
            if($e->getPrevious() && $e->getPrevious()->getMessage() == 'router'){
                $file = __DIR__ROOT. '\router\web.php';
            }
           return $this->render_view("error.index",[
               "message" => $e->getMessage(),
               "line" => $e->getLine(),
               "file" => $file,
               "trace" => $e->getTraceAsString(),
               "code" => $e->getCode() ?? 500
           ]);
        }
    }
}