<?php
namespace Hola\Container;

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
    private $bindings = [];

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new Container();
        }
        return self::$instance;
    }

    private function get($abstract) {
        return isset($this->bindings[$abstract]) ? $this->bindings[$abstract]:$abstract;
    }

    public function set($abstract, $factory = null): void
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
    }

    public function make($abstract, $factory = null) {
        return $this->build($abstract, $factory = null);
    }

    private function getClosure($factory)
    {
        return function () use ($factory) {
            return $this->build($factory);
        };
    }

    /**
     * @param $callable -- string class and method name
     * @param  array  $parameters
     */
    public function call($callable)
    {
        // set class name with namespace and method name
        try {
            $this->resolveCallback($callable);

            if (empty($this->callbackClass)) {
                throw new \TypeError(self::class.'::call(): Class must not be empty');
            }

            $methodReflection = new \ReflectionMethod($this->callbackClass, $this->callbackMethod);
            $methodParams = $methodReflection->getParameters();
            $dependencies = [];

            // loop with dependencies/parameters
            foreach ($methodParams as $param) {
                $type = $param->getType(); // check type
                if ($type && $type instanceof \ReflectionNamedType) { /// if parameter is a class
                    $name = $param->getClass()->newInstance(); // create insrance
                    array_push($dependencies, $name); // push  to $dependencies array
                }
            }

            foreach ($this->callbackMethodParams as $value) {
                array_push($dependencies, $value);
            }

            // make class instance
            $initClass = $this->build($this->callbackClass);

            // call method with $dependencies/parameters
            return $methodReflection->invoke($initClass, ...$dependencies);
        } catch (\Throwable $exception) {
            throw new \BadMethodCallException($exception->getMessage());
        }
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


    private function build($class)
    {
        try {
            $classReflection = new \ReflectionClass($class);
        } catch (\Exception $e) {
            throw new \Exception("Target class [$class] does not exist.", 0, $e);
        }

        $constructor = $classReflection->getConstructor();

        if (is_null($constructor)) {
            return new $class;
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

    private function resolveConstructorDependencies(array $dependencies): array
    {
        $array = [];
        foreach ($dependencies as $dependency) {
            $class = $this->getReflectionClassFromParameter($dependency);
            if ($class instanceof \ReflectionClass) {
                $abstract = $class->getName();
                $array[$dependency->getName()] = $this->build($this->get($abstract));
            }
        }
        return $array;
    }


    public function getReflectionClassFromParameter($parameter): ?\ReflectionClass
    {
        return $parameter->getType() && !$parameter->getType()->isBuiltin()
            ? new \ReflectionClass($parameter->getType()->getName())
            : null;
    }

}