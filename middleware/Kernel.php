<?php
namespace System\Middleware;
class Kernel {
    public $routerMiddleware = [
        "auth" => \System\Middleware\AuthMiddleware::class,
    ];
}