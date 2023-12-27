<?php
namespace App\Controllers;
use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validation;

class HomeController extends BaseController {
    public function __construct()
    {}

    public function index(Request $request){
        return Response::view('welcome');
    }

}