<?php
namespace App\Controllers;
use App\Models\Categories;
use App\Core\BaseController;
use App\Core\Database;
use App\Core\Request;

class HomeController extends BaseController {
    public function __construct()
    {
        $this->model([Categories::class]);
    }

    public function index(){
        echo 'index';
    }

    public function store(Request $request){
        log_debug(Categories::where('id','=',1)->toSqlRaw());
        echo 'store';
    }

    public function home(){
        echo 'home';
    }

    public function detail($id){
        echo $id;
    }

}