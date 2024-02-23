<?php
namespace System\Middleware;
use System\Core\Request;

class AuthMiddleware {
    // return boolean function
     public function handle(Request $request){
         if(!$request->session('is_login')){
            return $request->close('Login does not exit');
         }
         return $request->next();
     }
}