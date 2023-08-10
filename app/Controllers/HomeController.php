<?php
namespace App\Controllers;
use App\Models\Categories;
use Core\BaseController;
use Core\Database;
use Core\Request;

class HomeController extends BaseController {
    public function __construct()
    {}

    public function index(){
        echo 'index';
    }

    public function store(Request $request){
        echo 'store';
    }

    public function home(){
        echo 'home';
    }

    public function detail($id){
        echo $id;
    }

}