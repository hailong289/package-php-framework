<?php
namespace App\Controllers;
use Core\BaseController;
use Core\Request;

class HomeController extends BaseController {
    public function __construct()
    {}

    public function index($type = 1){
        echo 'index';
    }
    public function store(Request $request, $id){
        echo 'store';
    }
}