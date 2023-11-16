<?php
namespace App\Controllers;
use App\Models\Categories;
use App\Core\BaseController;
use App\Core\Database;
use App\Core\Request;

class HomeController extends BaseController {
    public function __construct()
    {}

    public function index(){
        echo 'Welcome';
    }

}