<?php
use Core\Router;
use App\Controllers\HomeController;

Router::get('/home', [HomeController::class,'store']);
Router::get('/home/{id}', [HomeController::class,'store']);
Router::get('/home/{id}/{name}', [HomeController::class,'index']);

