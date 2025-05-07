<?php
namespace Hola\Container;
use Hola\Events\AppEvents;
use Hola\Exceptions\AppException;
use Hola\Transport\Request;
use Hola\Transport\Response;

class Container
{
    /**
     * The container's  instance.
     *
     * @var static
     */
    protected static $instance;
    /**
     * @var array
     */
    protected $bindings = [];

    /**
     * @var array
     */
    protected $singletons = []; // store singleton instance

    /**
     * @return static
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new Container();
        }
        return self::$instance;
    }
    
    public function event(): AppEvents
    {
        if (!empty($this->singletons[AppEvents::class])) {
            return $this->singletons[AppEvents::class];
        }
        $this->singletons[AppEvents::class] = AppEvents::start();
        return $this->singletons[AppEvents::class];
    }

    /**
     * @return Request
     */
    public function request()
    {
        if (!empty($this->singletons[Request::class])) {
            return $this->singletons[Request::class];
        }
        $this->singletons[Request::class] = new Request();
        return $this->singletons[Request::class];
    }
    
    /**
     * @return Response
     */
    public function response() {
        if (!empty($this->singletons[Response::class])) {
            return $this->singletons[Response::class];
        }
        $this->singletons[Response::class] = new Response();
        return $this->singletons[Response::class];
    }

    /**
     * get class binding
     */
    private function getClass($abstract) {
        if (isset($this->singletons[$abstract])) {
            return $this->singletons[$abstract];
        }
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract];
        }
        return $abstract;
    }

    /**
     * @param $abstract
     * @param $factory
     * @return $this
     */
    public function set($abstract, $factory = null)
    {
        if (is_null($factory)) {
            $factory = $abstract;
        }
        if (!$factory instanceof \Closure) {
            if (!is_string($factory)) {
                throw new \TypeError(self::class.'::bind(): Argument #2 ($factory) must be of type Closure|string|null');
            }
            $factory = $this->getClosure($factory);
        }
        $this->bindings[$abstract] = $factory();
        return $this;
    }

    /**
     * @param $abstract
     * @param $factory
     * @return $this
     */
    public function singleton($abstract, $factory = null)
    {
        if (is_null($factory)) {
            $factory = $abstract;
        }
        if (!$factory instanceof \Closure) {
            if (!is_string($factory)) {
                throw new \TypeError(self::class.'::bind(): Argument #2 ($factory) must be of type Closure|string|null');
            }
            $factory = $this->getClosure($factory);
        }
        $this->singletons[$abstract] = $factory();
        return $this;
    }

    /**
     * @param $abstract
     * @param $factory
     * @return void
     */
    public function replace($abstract, $factory): void
    {
        if (isset($this->bindings[$abstract])) {
            $this->bindings[$abstract] = $factory();
        }
    }

    /**
     * @template T
     * @param class-string<T> $abstract The class name to instantiate.
     * @param mixed $factory Optional factory to resolve the instance.
     * @return T The instantiated object of the class.
     */
    public function make($abstract, $factory = null) {
        if (isset($this->singletons[$abstract])) {
            return $this->singletons[$abstract];
        }
        return $this->build($abstract);
    }

    /**
     * @param $factory
     * @return \Closure
     */
    private function getClosure($factory)
    {
        return function () use ($factory) {
            return $this->build($factory);
        };
    }


    /**
     * @template T
     * @param class-string<T> $abstract The class name to instantiate.
     * @param array $params Additional parameters to pass to the constructor.
     * @return T The instantiated object of the class.
     */
    public function callWithParams($abstract, $params = []) {
        return $this->build($abstract, $params);
    }

    /**
     * @var array
     */
    public function call($callable)
    {
        $this->resolveCallback($callable);

        if (empty($this->callbackClass)) {
            throw new \TypeError(self::class . '::call(): Class must not be empty');
        }
        $bindingClass = $this->callbackClass;
        try {
            $methodReflection = new \ReflectionMethod($bindingClass, $this->callbackMethod);
        } catch (\ReflectionException $e) {
            throw new \ReflectionException($e->getMessage(), 500);
        }
        $methodParams = $methodReflection->getParameters();
        $dependencies = [];

        // loop with dependencies/parameters
        foreach ($methodParams as $param) {
            $type = $param->getType(); // check type
            if ($type && $type instanceof \ReflectionNamedType) { /// if parameter is a class
                $className = $type->getName();
                $bindingClassMethod = $this->getClass($className);
                if (is_string($bindingClassMethod)) {
                    $bindingClassMethod = $this->make($bindingClassMethod);
                }
                array_push($dependencies, $bindingClassMethod); // push  to $dependencies array
            }
        }
        foreach ($this->callbackMethodParams as $value) {
            array_push($dependencies, $value);
        }
        // make class instance
        $initClass = $this->make($bindingClass);
        // call method with $dependencies/parameters
        return $methodReflection->invoke($initClass, ...$dependencies);
    }


    /**
     * separate class and method name
     * @param $callback
     */
    private function resolveCallback($callback)
    {
        //separate class and method
        if (is_string($callback)) {
            $segments = explode('@', $callback);
            $segments[0] = 'App\\Controllers\\' . $segments[0];
        } else {
            $segments = $callback;
        }
        // set class name with namespace
        $this->callbackClass = $segments[0];
        unset($segments[0]);

        // set method name . if method name not provided then default method __invoke
        if (isset($segments[1])) {
            $this->callbackMethod = $segments[1];
            unset($segments[1]);
        } else {
            $this->callbackMethod = '__invoke';
        }
        // set method params
        if (isset($segments[2])) {
            $this->callbackMethodParams = array_values($segments);
        } else {
            $this->callbackMethodParams = [];
        }
    }

    /**
     * Build an instance of the given class, resolving dependencies as needed.
     *
     * @template T
     * @param class-string<T> $class The class name to instantiate.
     * @param array $params Additional parameters to pass to the constructor.
     * @return T The instantiated object of type T.
     * @throws \ReflectionException If the class cannot be reflected or instantiated.
     */
    private function build($class, $params = [])
    {
        if ($class instanceof \Closure) {
            return $class($this);
        }

        if (interface_exists($class)) {
            $class = $this->bindings[$class] ?? null;
            if (!$class) {
                throw new AppException("No concrete implementation found for interface $class");
            }
        }

        try {
            $classReflection = new \ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new \ReflectionException($e->getMessage(), 500);
        }

        $constructor = $classReflection->getConstructor();

        if (is_null($constructor)) {
            return $classReflection->newInstance();
        }
        
        if (!empty($params)) {
            return $classReflection->newInstanceArgs($params);
        }

        $dependencies = [];

        /*
         * loop with constructor parameters or dependency
         */
        $dependencies = $constructor->getParameters();

        $instances = $this->resolveConstructorDependencies($dependencies);

        // finally pass dependancy and param to class instance
        return $classReflection->newInstanceArgs($instances);
    }

    /**
     * @param array $dependencies
     * @return array
     */
    private function resolveConstructorDependencies(array $dependencies): array
    {
        $array = [];
        foreach ($dependencies as $dependency) {
            $class = $this->getReflectionClassFromParameter($dependency);
            if ($class instanceof \ReflectionClass) {
                $abstract = $class->getName();
                $array[$dependency->getName()] = $this->make($abstract);
            }
        }
        return $array;
    }


    /**
     * @param $parameter
     * @return \ReflectionClass|null
     */
    public function getReflectionClassFromParameter($parameter): ?\ReflectionClass
    {
        return $parameter->getType() && !$parameter->getType()->isBuiltin()
            ? new \ReflectionClass($parameter->getType()->getName())
            : null;
    }

}