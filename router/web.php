<?php
use App\Core\Router;
use App\Controllers\HomeController;


Router::get('/', [HomeController::class,'index']);
Router::post('/', [HomeController::class,'index']);