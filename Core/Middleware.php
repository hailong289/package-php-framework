<?php

namespace Hola\Core;

use Hola\Container\Container;
use Middleware\Kernel;

class Middleware {
    private $bindings = [];

    public function __construct($names) {
        $this->bindings = $names;
    }

    public function set($names)
    {
        $this->bindings = $names;
        return $this;
    }
    
    public function bind($name)
    {
        $this->bindings[] = $name;
        return $this;
    }

    public function work() {
        if (class_exists(\Middleware\Kernel::class)) {
            $kernel = new \Middleware\Kernel();
            $container = new Container();
            try {
                $result = null;
                foreach ($this->bindings as $name) {
                    if(!empty($kernel->routerMiddleware[$name])){
                        $class = $kernel->routerMiddleware[$name];
                        $result = $container->call([$class, 'handle']);
                        break;
                    } else {
                        if (class_exists("\\Middleware\\$name")) {
                            $result = $container->call(["\\Middleware\\$name", 'handle']);
                            break;
                        } else {
                            throw new \RuntimeException("Middleware $name does not exist");
                        }
                    }
                }
                return $result;
            } catch (\Throwable $exception) {
                throw $exception;
            }
        }
    }
}