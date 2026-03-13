<?php

namespace Hola;
use App\Http\Middleware\Kernel;
use Hola\Connection\ConnectionManager;
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
        
        $this->singleton(Response::class, function () {
            return new Response();
        });

        $this->singleton(Router::class, function () {
            return new Router();
        });
        
        $this->singleton(AppEvents::class, function () {
            return AppEvents::start();
        });

        $this->singleton(ConnectionManager::class, function () {
            return new ConnectionManager();
        });
        
        return $this;
    }

    /**
     * Initialize the core components of the application.
     * @throws AppException
     */
    private function initializeCore()
    {
        $this->registerDependencies();
        $this->register();
        $this->registerEvent();
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
        $this->run();
    }

    /**
     * Run the application.
     * @return $this
     */
    public function run()
    {
        return $this->initializeCore()->handleHttpRequest();
    }



    /**
     * Run the application in CLI mode.
     * @return $this
     */
    public function runCLI()
    {
        $this->registerCommand();
        $this->cli->run();
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
    private function registerCommand()
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
            return $response();
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

}