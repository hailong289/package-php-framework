<?php
namespace App\Middleware;
class Kernel {
    public $routerMiddleware = [
        "auth" => \App\Middleware\AuthMiddleware::class,
    ];
}