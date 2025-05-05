<?php

namespace Hola;
use App\Http\Middleware\Kernel;
use Hola\Container\Container;
use Hola\Events\AppEvents;
use Hola\Exceptions\AppException;
use Hola\Exceptions\RouterException;
use Hola\Scripts\CliCommand;
use Hola\Transport\Request;
use Hola\Transport\Response;
use Hola\Routings\Router;
use Hola\Transport\ResponseBuilder;

class Application extends Container
{
    /** @var array|null */
    private $control;

    /** @var array|null */
    private $middlewares;

    /** @var CliCommand|null */
    private ?CliCommand $cli = null;

    public function __construct(){}
    
    public function register(){}
    
    public function registerEvent() {}

    /**
     * Registers application dependencies.
     *
     * @throws AppException
     */
    private function registerDependencies()
    {
        $this->singleton(Request::class, function () {
            return new Request();
        });

        $this->singleton(Router::class, function () {
            return new Router();
        });
        
        $this->singleton(AppEvents::class, function () {
            return AppEvents::start();
        });
        
        return $this;
    }

    /**
     * Register the application shutdown function.
     * @throws AppException
     */
    protected function registerShutdown(): void
    {
        register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * Initialize the core components of the application.
     * @throws AppException
     */
    public function initializeCore()
    {
        $this->register();
        $this->registerDependencies();
        $this->registerRouter();
        $this->registerMiddleware();
        return $this;
    }

    /**
     * Registers the application router.
     *
     * @throws AppException
     */
    public function testRun() {
        try {
            $this->run();
        } catch (\Throwable $e) {
            echo $e;
        }
    }

    /**
     * Run the application.
     * @return $this
     */
    public function run()
    {
        try {
            $this->registerShutdown();
            return $this->initializeCore()
                ->handleHttpRequest();
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }



    /**
     * Run the application in CLI mode.
     * @return $this
     */
    public function runCLI()
    {
        try {
            $this->registerShutdown();
            $this->registerCommand();
            $this->cli->run();
        } catch (\Throwable $e) {
            $this->handleErrorLogs($e);
            echo $e->getMessage() . PHP_EOL . $e->getTraceAsString();
        }
        return $this;
    }

    /**
     * Set the header to JSON.
     */
    public function setHeaderJson()
    {
        header('Content-Type: application/json; charset=utf-8');
    }

    /**
     * Register the application commands.
     * @throws AppException
     * @return void
     */
    public function registerCommand()
    {
        $this->cli = app()->make(CliCommand::class)
            ->initAppCommand()
            ->register();
    }

    /**
     * Run the application.
     * @return $this
     */
    private function handleHttpRequest()
    {
        try {
            if (empty($this->control)) {
                throw new AppException("Class controller in router does not exit", 500);
            }
            $middleware = $this->resolveMiddleware();
            if (!empty($middleware['return'])) {
                return $this->responseSuccess($middleware['return']);
            }
            $control_array = array_values($this->control);
            $result = $this->call($control_array);
            return $this->responseSuccess($result);
        } catch (\Throwable $e) {
            return $this->responseError($e);
        }
    }

    /**
     * Handle the exception.
     * @return void
     */
    public function handleException(\Throwable $e)
    {
        return $this->responseError($e);
    }

    /**
     * Handle the response core.
     * @param mixed $return
     * @return $this
     */
    private function responseCore($response) {
        if ($response instanceof ResponseBuilder) {
            return $response->send();
        }

        if (is_array($response) || is_object($response)) {
            return Response::json($response)->send();
        }

        if (is_string($response)) {
            if (is_file($response)) {
                return Response::file($response)->send();
            }

            return Response::text($response)->send();
        }

        if ($response instanceof \Closure) {
            return $this->handleClosure($response);
        }

        return Response::text('Invalid response')->send();
    }

    /**
     * Handle the response success.
     * @param mixed $return
     * @return $this
     */
    private function responseSuccess($return)
    {
        return $this->responseCore($return);
    }

    /**
     * Handle the response error.
     * @param \Throwable $e
     * @return $this
     */
    private function responseError(\Throwable $e)
    {
        $this->handleErrorLogs($e);
        $app_debug = conval('APP_DEBUG', false);
        if (!$app_debug) {
            $code = $this->getStatusCode($e->getCode());
            $errors = [
                "message" => $code === 500 ? "Internal Server Error" : $e->getMessage(),
                "code" => $code
            ];
        } else {
            $errors = [
                "message" => $e->getMessage(),
                "code" => $this->getStatusCode($e->getCode()),
                "line" => $e->getLine(),
                "file" => $e->getFile(),
                "trace" => $e->getTraceAsString(),
                "previous" => $e->getPrevious()
            ];
        }
        if ($this->isJson()) {
            $res = Response::json($errors)->setStatus($errors['code']);
            return $this->responseCore($res);
        }
        $res = Response::view('error.index', $errors)->setStatus($errors['code']);
        return $this->responseCore($res);
    }

    /**
     * Check if the request is JSON.
     * @return bool
     */
    private function isJson()
    {
        try {
            return app()->request()->isJson();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Write the error logs.
     * @param \Throwable $e
     * @return void
     */
    private function handleErrorLogs(\Throwable $e)
    {
        $storagePath = __DIR__ROOT . '/storage';
        if (!file_exists($storagePath) && !mkdir($storagePath, 0777, true) && !is_dir($storagePath)) {
            echo sprintf('Directory "%s" was not created', $storagePath);
            return;
        }

        $storagePath = __DIR__ROOT . '/storage';
        $logFile = "$storagePath/application.log";

        $errorMessage = sprintf(
            "[%s][%d]: %s in %s on line %d\n%s\n\n",
            date('Y-m-d H:i:s'),
            $this->getStatusCode($e->getCode()),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        app()->event()->trigger('app.exceptions', [
            'type' => 'event_exceptions',
            'message' => $e->getMessage(),
            'code' => $this->getStatusCode($e->getCode()),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
            'trace' => $e->getTraceAsString(),
            'class' => get_class($e),
            'previous' => $e->getPrevious()
        ]);

        file_put_contents($logFile, $errorMessage, FILE_APPEND | LOCK_EX);
    }

    /**
     * Register the application router.
     * @throws AppException
     */
    private function registerRouter()
    {
        $router = $this->make(Router::class)->handle();
        if (empty($router)) {
            throw new RouterException("No route found!", 404);
        }
        $this->control = $router['controls'];
        $this->middlewares = $router['middlewares'];
        return $this;
    }

    /**
     * Register the application middleware.
     * @throws AppException
     */
    private function registerMiddleware()
    {
        $kernel = app(Kernel::class);
        foreach ($kernel->getRequiredMiddleWares() as $middleware) {
            if (empty($this->middlewares)) {
                $this->middlewares = [$middleware];
            } else {
                $this->middlewares[] = $middleware;
            }
        }
        return $this;
    }

    /**
     * Resolve the middleware.
     * @return array
     */
    private function resolveMiddleware()
    {
        if (empty($this->middlewares)) {
            return [];
        }
        return $this
            ->make(\Hola\Transport\MiddlewareBuilder::class)
            ->handle(fn() => [$this->middlewares, $this]);
    }

    /**
     * Get the status code.
     * @param int $code
     * @return int
     */
    private function getStatusCode($code)
    {
        $code = (int)$code;
        return $code ? $code : 500;
    }

    /**
     * Handle the shutdown.
     * @return void
     */
    private function handleShutdown()
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->handleErrorLogs(new \ErrorException(
                $error['message'],
                $error['type'],
                0,
                $error['file'],
                $error['line']
            ));
        }
    }

    private function handleClosure(\Closure $closure) {
        try {
            return $closure();
        } catch (\Exception $e) {
            return Response::text('Error executing closure: ' . $e->getMessage())->send();
        }
    }
}