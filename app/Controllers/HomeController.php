<?php
namespace App\Controllers;
use App\Models\Categories;
use System\Core\BaseController;
use System\Core\Database;
use System\Core\Request;
use System\Core\Response;
use System\Queue\CreateQueue;
use System\Queue\Job1;

class HomeController extends BaseController {
    public function __construct()
    {}

    public function index(Request $request){
        return Response::view('welcome');
    }

}