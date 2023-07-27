<?php
use Core\Router;
use App\Controllers\HomeController;

Router::get('/', [HomeController::class,'index']);
Router::get('/home', [HomeController::class,'index']);
Router::get('/home/detail', [HomeController::class,'index2']);
Router::get('/home/{id}', [HomeController::class,'store']);
Router::get('/home2/{id}', [HomeController::class,'storeWithID']);
Router::get('/home/{id}/{name}', [HomeController::class,'index']);

