<?php
namespace App\Controllers;
use App\BaseController;

class HomeController extends BaseController {
    public function __construct()
    {
        echo 'home';
    }

    public function index($type = 1){
        echo 'index';
    }
}