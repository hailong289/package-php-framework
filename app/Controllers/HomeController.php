<?php
namespace App\Controllers;
use App\Models\Categories;
use Core\BaseController;
use Core\Request;

class HomeController extends BaseController {
    public function __construct()
    {}

    public function index(){
        echo 'index';
        $this->render_view('name_file', ["title" => "Home"]);
    }
    public function store(Request $request, $id){
        echo 'store';
    }
    public function home(){
        echo 'home';
    }

    public function detail($id){
        echo $id;
    }

}