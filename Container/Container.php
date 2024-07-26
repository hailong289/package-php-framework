<?php
namespace Hola\Container;

class Container
{

    public function make($abstract, $class = null)
    {
        if (is_null($class)) {
            $class = $abstract;
        }

        return $this->build($class);
    }

    public function build(string $class)
    {
        try {
            $reflector = new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new \Exception("Target class [$class] does not exist.", 0, $e);
        }

        // If the type is not instantiable, such as an Interface or Abstract Class
        if (! $reflector->isInstantiable()) {
            throw new \Exception("Target [$class] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        // If there are no constructor, that means there are no dependencies
        if ($constructor === null) {
            return new $class;
        }

        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
                // Resolve a non-class hinted primitive dependency.
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else if ($parameter->isVariadic()) {
                    $dependencies[] = [];
                } else {
                    throw new \Exception("Unresolvable dependency [$parameter] in class {$parameter->getDeclaringClass()->getName()}");
                }
            }

            $name = $type->getName();

            // Resolve a class based dependency from the container.
            try {
                $dependency = $this->get($name);
                $dependencies[] = $dependency;
            } catch (\Exception $e) {
                if ($parameter->isOptional()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    $dependency = $this->build($parameter->getType()->getName());
                    $this->set($name, $dependency);
                    $dependencies[] = $dependency;
                }
            }
        }

        return $reflector->newInstanceArgs($dependencies);
    }

}