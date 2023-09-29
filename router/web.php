<?php
use App\Core\Router;
use App\Controllers\HomeController;



Router::middleware(['auth'])->group(function (){
    Router::get('/home', [HomeController::class,'home']);
    Router::get('/home/store', [HomeController::class,'store']);
//    Router::get('/home/{id}/detail/{name}', [HomeController::class,'detail']);
    Router::prefix('home')->group(function () {
        Router::get('/{id}/detail', [HomeController::class,'detail']);
    });
    Router::prefix('blog')->group(function () {
        Router::get('/{id}/detail', [HomeController::class,'store']);
    });
});

Router::middleware(['auth'])->group(function (){
    Router::prefix('prefix')->group(function (){
        Router::get('name', [HomeController::class,'index']);
    });
});

Router::get('/', [HomeController::class,'store']);