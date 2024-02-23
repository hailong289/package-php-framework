<?php
namespace System;
use System\Core\Request;
use System\Core\Response;
use System\Core\Router;

class App {
    private $__controller;
    private $__action;
    private $__param = [];
    private $router;
    public function __construct(){}
    private function handleUrl(){
        try {
            $url = $this->router->url();
            $urlarr = array_values($url);
            if (!empty($urlarr[0])) {
                $this->__controller = $urlarr[0];
                if (class_exists($this->__controller)) {
                    $this->__controller = new $this->__controller();
                } else {
                    throw new \RuntimeException("{$this->__controller} does not exit", 500);
                }
                unset($urlarr[0]);
            } else {
                throw new \RuntimeException('Page not found', 404);
            }
            if (isset($urlarr[1])) {
                $this->__action = $urlarr[1];
                unset($urlarr[1]);
            }
            $this->__param = array_values($urlarr);
            if (method_exists($this->__controller, $this->__action)) {
                // xử lý method
                $method = new \ReflectionMethod($this->__controller, $this->__action);
                $agr = [];
                foreach ($method->getParameters() as $ag){
                    if($ag->name == 'request'){
                        array_push($agr, new Request());
                    }else{
                        $agr = [...$agr,...$this->__param];
                    }
                }
                $result = $this->__controller->{$this->__action}(...$agr);
                return [
                    "error_code" => 0,
                    "return" => $result
                ];
            }else{
                $controller = serialize($this->__controller);
                throw new \RuntimeException("Method {$this->__action} does not exit in controller {$controller}",500);
            }
        }catch (\Throwable $e){
            $code = (int)$e->getCode();
            $code = $code ? $code : 500;
            if (DEBUG_LOG) $this->write_logs_error($e);
            http_response_code($code);
            return [
                "error_code" => 1,
                "return" => [
                    "message" => $e->getMessage(),
                    "line" => $e->getLine(),
                    "file" => $e->getFile(),
                    "code" => $code
                ],
                "view" => "error.index"
            ];
        }
    }
    public function run() {
        try {
            date_default_timezone_set(TIMEZONE);
            $this->router = new Router();
            $resultHandle = $this->handleUrl();
            if($resultHandle['error_code'] === 0){
                if(is_array($resultHandle['return']) || is_object($resultHandle['return'])) {
                    echo json_encode($resultHandle['return']);
                } else {
                    echo $resultHandle['return'];
                }
                return $this;
            }

            if ($resultHandle['error_code'] === 1) {
                return Response::view($resultHandle['view'], $resultHandle['return']);
            }
        }catch (\Throwable $e) {
            throw $e;
        }
    }

    private function write_logs_error($e): void {
        $date = "[".date('Y-m-d H:i:s')."]: ";
        if (!file_exists(__DIR__ROOT .'/storage')) {
            if (!mkdir($concurrentDirectory = __DIR__ROOT . '/storage', 0777, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }
        file_put_contents(__DIR__ROOT .'/storage/debug.log',$date . $e . PHP_EOL.PHP_EOL, FILE_APPEND);
    }
}