<?php
namespace App\Controllers;

class HomeController {
    public function __construct()
    {
        echo 'home';
    }

    public function index($type = 1){
        echo 'index';
    }
}