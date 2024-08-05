<?php

namespace Hola;

use Hola\Container\Container;
use Hola\Core\Request;
use Hola\Core\Response;
use Hola\Core\Router;

class Application extends Container
{
    private $control;

    public function __construct()
    {
        if ($this->isJson()) {
            $this->setHeaderJson();
        }
    }

    public function register()
    {
        $this->set(Router::class, function () {
            return new Router();
        });
        $this->set(Request::class, function () {
            return new Request();
        });
    }

    public function run()
    {
        try {
            $this->register();
            $this->work();
        } catch (\Throwable $e) {
            $this->handleErrorLogs($e);
            $errors = $this->errorDefault($e);
            return $this->responseError($errors, $code);
        }
    }

    public function setHeaderJson()
    {
        header('Content-Type: application/json; charset=utf-8');
    }
    
    public function registerCommand()
    {
        $app = new \Symfony\Component\Console\Application();
        $command_dir = glob(__DIR__ROOT .'/commands/*.php');
        $array_command = [];
        if (!empty($command_dir)) {
            foreach($command_dir as $item){
                $item = str_replace('.php','',$item);
                $class = str_replace('commands/','\Commands\\',$item);
                $array_command[] = $this->make($class);
            }
        }
        $array_command = array_merge($array_command, [
            $this->make(\Hola\Scripts\ControllerScript::class),
            $this->make(\Hola\Scripts\ModelScript::class),
            $this->make(\Hola\Scripts\ViewScript::class),
            $this->make(\Hola\Scripts\RequestScript::class),
            $this->make(\Hola\Scripts\MiddlewareScript::class),
            $this->make(\Hola\Scripts\QueueScript::class),
            $this->make(\Hola\Scripts\CommandScript::class),
            $this->make(\Hola\Scripts\MailScript::class),
            $this->make(\Hola\Scripts\RouterScript::class),
            $this->make(\Hola\Scripts\CacheScript::class),
        ]);
        foreach ($array_command as $item) {
            $app->add($item);
        }
        $app->run();
    }

    private function work()
    {
        try {
            $control = $this->make(Router::class)->url();
            $control_array = array_values($control);
            if (empty($control_array)) {
                throw new \RuntimeException("Class controller in router does not exit", 500);
            }
            $result = $this->call($control_array);
            return $this->responseSuccess($result, 200);
        } catch (\Throwable $e) {
            $this->handleErrorLogs($e);
            $code = (int)$e->getCode();
            $code = $code ? $code : 500;
            $errors = $this->errorDefault($e);
            return $this->responseError($errors, $code);
        }
    }


    private function responseSuccess($return, $code)
    {
        if (is_array($return) || is_object($return)) {
            echo json_encode($return);
        } else if (is_file($return)) { // return file
            return $this;
        } else {
            http_response_code($code);
            echo $return;
        };
        return $this;
    }

    private function errorDefault($e)
    {
        $code = (int)$e->getCode();
        $code = $code ? $code : 500;
        $errors = [
            "message" => $e->getMessage(),
            "line" => $e->getLine(),
            "file" => $e->getFile(),
            "trace" => $e->getTraceAsString(),
            "code" => $code
        ];
        return $errors;
    }

    private function responseError($return, $code)
    {
        if ($this->isJson()) {
            echo json_encode($return);
            return $this;
        }
        return Response::view('error.index', $return, [], $code);
    }

    private function isJson()
    {
        return $this->make(Request::class)->isJson();
    }

    private function handleErrorLogs($e)
    {
        $enable_db = config_env('DEBUG_LOG', false);
        if (!$enable_db) return;
        $date = "[" . date('Y-m-d H:i:s') . "]: ";
        if (!file_exists(__DIR__ROOT . '/storage')) {
            if (!mkdir($concurrentDirectory = __DIR__ROOT . '/storage', 0777, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }
        file_put_contents(__DIR__ROOT . '/storage/debug.log', $date . $e . PHP_EOL . PHP_EOL, FILE_APPEND);
    }

}