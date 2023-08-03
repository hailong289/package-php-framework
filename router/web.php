<?php
use Core\Router;
use App\Controllers\HomeController;



Router::middleware(['auth'])->group(function (){
    Router::get('/home', [HomeController::class,'home']);
    Router::get('/home/detail', [HomeController::class,'index2']);
    Router::get('/home/{id}', [HomeController::class,'store']);
    Router::get('/home2/{id}', [HomeController::class,'storeWithID']);
    Router::get('/home/{id}/{name}', [HomeController::class,'index']);
});

Router::prefix('home3')->group(function (){
    Router::get('{id}', [HomeController::class,'store']);
});

Router::get('/', [HomeController::class,'index']);