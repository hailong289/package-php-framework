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
    protected $bindings = [
        'alias' => null,
        'method' => [
            'method' => null,
            'params' => []
        ],
        'resolved' => []
    ];

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
        if ($this->resolved(AppEvents::class)) {
            return $this->getResolved(AppEvents::class);
        }
        return $this->build(AppEvents::class);
    }

    /**
     * @return Request
     */
    public function request()
    {
        if ($this->resolved(Request::class)) {
            return $this->getResolved(Request::class);
        }
        return $this->build(Request::class);
    }
    
    /**
     * @return Response
     */
    public function response() {
        if ($this->resolved(Response::class)) {
            return $this->getResolved(Response::class);
        }
        return $this->build(Response::class);
    }

    /**
     * get class binding
     */
    private function getClass($abstract) {
        if (isset($this->singletons[$abstract])) {
            return $this->singletons[$abstract];
        }
        return $abstract;
    }

    /**
     * @param $abstract
     * @return bool
     */
    public function bound($abstract)
    {
        return isset($this->singletons[$abstract]);
    }

    /**
     * @param $abstract
     * @return bool
     */
    public function resolved($abstract)
    {
        return isset($this->bindings['resolved'][$abstract]);
    }

    /**
     * @param $abstract
     * @param $factory
     * @return $this
     */
    public function getResolved($abstract)
    {
        return $this->bindings['resolved'][$abstract];
    }

    /**
     * @param $abstract
     * @param $factory
     * @return $this
     */
    public function singleton($abstract, $factory = null)
    {
        $this->singletons[$abstract] = $this->bind($abstract, $factory);
        return $this;
    }

    /**
     * @template T
     * @param class-string<T> $abstract The class name to instantiate.
     * @param array $params Additional parameters to pass to the constructor.
     * @return T The instantiated object of the class.
     */
    public function make($abstract, $params = []) {
        return $this->build($abstract, $params);
    }

    /**
     * @param $abstract
     * @param $factory
     * @return \Closure
     */
    private function bind($abstract, $factory = null)
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
        return $factory;
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
     * @var array
     */
    public function call($callable)
    {
        $this->resolveBindAliasWithMethod($callable);

        if (empty($this->bindings['alias'])) {
            throw new \TypeError(self::class.'::call(): Argument #1 ($callable) must be of type string|array');
        }

        try {
            $methodReflection = new \ReflectionMethod($this->bindings['alias'], $this->bindings['method']['name']);
        } catch (\ReflectionException $e) {
            throw new \ReflectionException($e->getMessage(), 500);
        }
        $methodParams = $methodReflection->getParameters();
        $dependencies = [];

        // loop with dependencies/parameters
        foreach ($methodParams as $param) {
            $type = $param->getType(); // check type
            if ($type && $type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                $className = $type->getName();
                $bindingClassMethod = $this->getClass($className);
                if (is_string($bindingClassMethod)) {
                    $bindingClassMethod = $this->make($bindingClassMethod);
                } elseif ($bindingClassMethod instanceof \Closure) {
                    $bindingClassMethod = $bindingClassMethod();
                } elseif ($bindingClassMethod instanceof \ReflectionClass) {
                    $bindingClassMethod = $this->make($bindingClassMethod->getName());
                }
                $dependencies[$param->getName()] = $bindingClassMethod;
            } elseif (!empty($this->bindings['method']['params']) && $param->isDefaultValueAvailable()) {
                $dependencies[$param->getName()] = array_shift($this->bindings['method']['params']);
            } else {
                throw new AppException("Missing required parameter: {$param->getName()}");
            }
        }
        foreach ($this->bindings['method']['params'] as $value) {
            array_push($dependencies, $value);
        }
        // call method with $dependencies/parameters
        return $methodReflection->invoke($this->make($this->bindings['alias']), ...$dependencies);
    }


    /**
     * separate class and method name
     * @param $callback
     */
    private function resolveBindAliasWithMethod($callback)
    {
        //separate class and method
        if (is_string($callback)) {
            $segments = explode('@', $callback);
            $segments[0] = 'App\\Controllers\\' . $segments[0];
        } else {
            $segments = $callback;
        }
        // set class name with namespace
        $this->bindings['alias'] = $segments[0];
        unset($segments[0]);

        // set method name . if method name not provided then default method __invoke
        if (isset($segments[1])) {
            $this->bindings['method']['name'] = $segments[1];
            unset($segments[1]);
        } else {
            $this->callbackMethod = '__invoke';
            $this->bindings['method']['name'] = '__invoke';
        }
        // set method params
        if (isset($segments[2])) {
            $this->callbackMethodParams = array_values($segments);
            $this->bindings['method']['params'] = array_values($segments);
        } else {
            $this->callbackMethodParams = [];
            $this->bindings['method']['params'] = [];
        }
    }

    /**
     * Build an instance of the given class, resolving dependencies as needed.
     *
     * @template T
     * @param class-string<T> $abstract The class name to instantiate.
     * @param array $params Additional parameters to pass to the constructor.
     * @return T The instantiated object of type T.
     * @throws \ReflectionException If the class cannot be reflected or instantiated.
     */
    private function build($abstract, $params = [])
    {
        $abstract = $this->getClass($abstract);

        if ($abstract instanceof \Closure) {
            return $abstract();
        }

        try {
            $classReflection = new \ReflectionClass($abstract);
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
        $this->bindings['resolved'][$className] = $classReflection->newInstanceArgs($instances);
        return $this->bindings['resolved'][$className];
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
            } elseif ($dependency->isDefaultValueAvailable()) {
                $array[$dependency->getName()] = $dependency->getDefaultValue();
            } else {
                throw new AppException("Unresolvable dependency: {$dependency->getName()} in {$dependency->getDeclaringClass()->getName()}");
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