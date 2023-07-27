<?php
namespace App\Controllers;
use App\Models\Categories;
use Core\BaseController;
use Core\Request;

class HomeController extends BaseController {
    public function __construct()
    {}

    public function index($type = 1){
        $this->model(Categories::class)::index();
        echo 'index';
    }
    public function store(Request $request, $id){
        echo 'store';
    }
}