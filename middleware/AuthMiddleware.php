<?php
namespace App\Middleware;

use App\Core\Response;
use App\Core\Session;
use App\Core\Request;

class Auth {
    // return boolean function
     public function handle(Request $request){
         if(!$request->session('auth')){
            return $request->next('Login does not exit');
         }
         return $request->next();
     }
}