<?php
namespace App\Middleware;

use Core\Request;

class Auth {
     public function handle(Request $request){
         return false;
     }
}