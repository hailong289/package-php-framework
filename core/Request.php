<?php
namespace Core;
class Request {
    public $file = '';
    
    public function get($key = ''){
        $method = $_SERVER['REQUEST_METHOD'];
        $is_get = $method == 'GET' ? true:false;
        $is_post = $method == 'POST' ? true:false;
        $is_patch = $method == 'PATCH' ? true:false;
        $is_put = $method == 'PUT' ? true:false;
        if($is_get){
            return $_GET[$key] ?? null;
        }elseif($is_post){
            return $_POST[$key] ?? null;
        }elseif($is_patch){
            return $this->patch($key);
        }else{
            return $this->put($key);
        }
    }

    private function patch($key = '', $all = false){
        $_PATCH = file_get_contents('php://input');
        if($all) return $_PATCH;
        return $_PATCH[$key] ?? null;
    }

    private function put($key = '', $all = false){
        $_PUT = file_get_contents('php://input');
        if($all) return $_PUT;
        return $_PUT[$key] ?? null;
    }

    public function file($key = ''){
        if(!array_key_exists($key, $_FILES)) die('key not exit');
        $this->file = $_FILES[$key];
    }
    public function tmp_name(){
        if(empty($this->file)) die('key not exit');
        return $this->file['tmp_name'];
    }

    public function size(){
        if(empty($this->file)) die('key not exit');
        return $this->file['size'];
    }

    public function type(){
        if(empty($this->file)) die('key not exit');
        return $this->file['type'];
    }

    public function error(){
        if(empty($this->file)) die('key not exit');
        return $this->file['error'];
    }

    public function originName(){
        if(empty($this->file)) die('key not exit');
        $array_file = explode(".", $this->file['name']);
        return end($array_file);
    }

    public function extension(){
        if(empty($this->file)) die('key not exit');
        return current((explode(".", $this->file['name'])));
    }

    public function isFile($key = ''){
        if(!file_exists($_FILES[$key]['tmp_name'])) {
            return false;
        }
        return true;
    }
    public function all(){
        $method = $_SERVER['REQUEST_METHOD'];
        $is_get = $method == 'GET' ? true:false;
        $is_post = $method == 'POST' ? true:false;
        $is_patch = $method == 'PATCH' ? true:false;
        $is_put = $method == 'PUT' ? true:false;
        if($is_get){
            return $_GET;
        }elseif($is_post){
            return $_POST;
        }elseif($is_patch){
            return $this->patch('', true);
        }else{
            return $this->put('', true);
        }
    }

    public function session($key = ''){
        return $_SESSION[$key] ?? null;
    }

    public function cookie($key = ''){
        return $_COOKIE[$key] ?? null;
    }

    public function headers($key){
        $headers = getallheaders();
        return $headers[$key] ?? null;
    }

    public function next($string = ''){
        return (object)[
            "message" => $string,
            "error_code" => 0
        ];
    }

    public function close($string = ''){
        return (object)[
            "message" => $string,
            "error_code" => 1
        ];
    }
}