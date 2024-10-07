<?php
namespace Hola\Core;
class BaseController {
    public function middleware($name)
    {
        $middleware = new Middleware();
        $middleware->set($name);
    }
}