<?php
namespace App\Middleware;
class Kernel {
    public $routerMiddleware = [
        "auth" => \App\Middleware\Auth::class,
    ];
}