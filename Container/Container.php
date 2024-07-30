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


    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new Container();
        }
        return self::$instance;
    }

    /**
     * @param $callable -- string class and method name
     * @param  array  $parameters
     */
    public function call($callable, $parameters = [])
    {
        // set class name with namespace and method name
        $this->resolveCallback($callable);
        $methodReflection = new \ReflectionMethod($this->callbackClass, $this->callbackMethod);
        $methodParams = $methodReflection->getParameters();

        $dependencies = [];

        // loop with dependencies/parameters
        foreach ($methodParams as $param) {

            $type = $param->getType(); // check type

            if ($type && $type instanceof \ReflectionNamedType) { /// if parameter is a class
                $name = $param->getClass()->newInstance(); // create insrance
                array_push($dependencies, $name); // push  to $dependencies array

            } else {  /// Normal parameter
                $name = $param->getName();

                if (array_key_exists($name, $parameters)) { // check exist in $parameters
                    array_push($dependencies, $parameters[$name]); // push  to $dependencies array
                }
            }

        }

        // make class instance
        $initClass = $this->make($this->callbackClass, $parameters);

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
        } else {
            $segments = $callback;
        }

        // set class name with namespace
        $this->callbackClass = $segments[0];

        // set method name . if method name not provided then default method __invoke
        $this->callbackMethod = isset($segments[1]) ? $segments[1] : '__invoke';

    }


    public function make($class, $parameters = [])
    {

        $classReflection = new \ReflectionClass($class);
        $constructorParams = $classReflection->getConstructor()->getParameters();
        $dependencies = [];

        /*
         * loop with constructor parameters or dependency
         */
        foreach ($constructorParams as $constructorParam) {

            $type = $constructorParam->getType();

            if ($type && $type instanceof \ReflectionNamedType) {

                // make instance of this class :
                $paramInstance = $constructorParam->getClass()->newInstance();

                // push to $dependencies array
                array_push($dependencies, $paramInstance);

            } else {

                $name = $constructorParam->getName(); // get the name of param
                // check this param value exist in $parameters
                if (array_key_exists($name, $parameters)) { // if exist
                    // push  value to $dependencies sequencially
                    array_push($dependencies, $parameters[$name]);

                }
            }

        }
        // finally pass dependancy and param to class instance
        return $classReflection->newInstance(...$dependencies);
    }

}