<?php
use Core\Router;
use App\Controllers\HomeController;



Router::middleware(['auth'])->group(function (){
    Router::get('/home', [HomeController::class,'home']);
    Router::get('/home/store', [HomeController::class,'store']);
    Router::get('/home/{id}/detail/{name}', [HomeController::class,'detail']);
});

Router::prefix('prefix')->group(function (){
    Router::get('name', [HomeController::class,'index']);
});

Router::get('/', [HomeController::class,'store']);