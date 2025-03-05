<?php

namespace Hola;
use Hola\Container\Container;
use Hola\Exceptions\AppException;
use Hola\Transport\Request;
use Hola\Transport\Response;
use Hola\Routings\Router;

class Application extends Container
{
    private $control;
    private $middlewares;
    private $cli;

    public function __construct(){
        if ($this->isJson()) {
            $this->setHeaderJson();
        }
    }
    
    public function register(){}

    public function registerDependencies()
    {
        $this->set(Request::class, function () {
            return new Request();
        });

        $this->singleton(Router::class, function () {
            return new Router();
        });

    }

    public function testRun() {
        try {
            $this->run();
        } catch (\Throwable $e) {
            echo $e;
        }
    }

    public function run()
    {
        try {
            $this->register();
            $this->registerDependencies();
            $this->registerRouter();
            $this->registerMiddleware();
            $this->work();
        } catch (\Throwable $e) {
            $this->handleErrorLogs($e);
            $code = (int)$e->getCode();
            $code = $code ? $code : 500;
            $errors = $this->errorDefault($e);
            return $this->responseError($errors, $code);
        }
    }

    public function runCLI()
    {
        if (empty($this->cli)) {
            $this->registerCommand();
        }
        $this->cli->run();
        return $this;
    }

    public function setHeaderJson()
    {
        header('Content-Type: application/json; charset=utf-8');
    }
    
    public function registerCommand()
    {
        $this->cli = new \Symfony\Component\Console\Application();
        $command_dir = scandir(__DIR__ROOT .'/App/Commands');
        $command_dir = array_diff($command_dir, array('.', '..'));

        $array_command = [];
        if (!empty($command_dir)) {
            foreach($command_dir as $item){
                $item = str_replace('.php','',$item);
                $array_command[] = $this->make("App\\Commands\\$item");
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
            $this->make(\Hola\Scripts\GenerateScript::class),
            $this->make(\Hola\Scripts\SchemaScript::class),
            $this->make(\Hola\Scripts\SchemaRunScript::class),
        ]);
        foreach ($array_command as $item) {
            $this->cli->add($item);
        }
    }



    private function work()
    {
        try {
            if (empty($this->control)) {
                throw new AppException("Class controller in router does not exit", 500);
            }
            $control_array = array_values($this->control);
            $result = $this->call($control_array);
            return $this->responseSuccess($result);
        } catch (\Throwable $e) {
            $this->handleErrorLogs($e);
            $errors = $this->errorDefault($e);
            return $this->responseError($errors);
        }
    }

    private function responseCore($return) {
        if ($return instanceof \SimpleXMLElement) {
            echo $return->asXML();
        } else if (is_array($return) || is_object($return)) {
            echo json_encode($return);
        } else if (is_file($return)) {
            echo file_get_contents($return);
        } else {
            echo $return;
        }
        return $this;
    }

    private function responseSuccess($return)
    {
        return $this->responseCore($return);
    }

    private function responseError($return)
    {
        if ($this->isJson()) {
            $res = Response::json($return, $return['code']);
        } else {
            $res = Response::view('error.index', $return, $return['code']);
        }
        return $this->responseCore($res);
    }

    private function errorDefault($e) {
        $code = (int)$e->getCode();
        $code = $code ? $code : 500;
        $errors = [
            "message" => $e->getMessage(),
            "code" => $code,
            "line" => $e->getLine(),
            "file" => $e->getFile(),
            "trace" => $e->getTraceAsString(),
            "previous" => $e->getPrevious()
        ];
        return $errors;
    }

    private function isJson()
    {
        try {
            return $this->make(Request::class)->isJson();
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function handleErrorLogs(\Throwable $e)
    {
        $enable_db = conval('DEBUG_LOG', false);
        if (!$enable_db) return;
        $date = "[" . date('Y-m-d H:i:s') . "][{$e->getCode()}]: ";
        if (!file_exists(__DIR__ROOT . '/storage')) {
            if (!mkdir($concurrentDirectory = __DIR__ROOT . '/storage', 0777, true) && !is_dir($concurrentDirectory)) {
                echo sprintf('Directory "%s" was not created', $concurrentDirectory);
            }
        }
        $stringError = "$date{$e->getMessage()} in {$e->getFile()} on line {$e->getLine()}". PHP_EOL;
        $stringError .= $e->getTraceAsString() . PHP_EOL . PHP_EOL;
        file_put_contents(__DIR__ROOT . '/storage/debug.log', $stringError, FILE_APPEND);
    }

    private function registerRouter()
    {
        $router = $this->make(Router::class)->handle();
        $this->control = $router['controls'];
        $this->middlewares = $router['middlewares'];
    }

    private function registerMiddleware()
    {
        $result = $this->make(\Hola\Transport\MiddlewareBuilder::class)->handle(fn() => [$this->middlewares, $this]);
        if (!empty($result[concat('', 'passable', PROJECT_KEY)])) {
            return false;
        }
        $this->responseSuccess($result);
        exit();
    }
}