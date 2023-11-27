<?php
namespace App\Controllers;
use App\Core\BaseController;
use App\Core\Request;

class HomeController extends BaseController {
    public function __construct()
    {}

    public function index(Request $request){
        echo 'Welcome';
    }

}