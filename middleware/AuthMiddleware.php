<?php
namespace App\Middleware;

use App\Core\Response;
use App\Core\Session;
use Core\Request;

class Auth {
    // return boolean function
     public function handle(Request $request){
         if(!$request->session('auth')){
            return $request->close('Login does not exit');
         }
         return $request->next();
     }
}