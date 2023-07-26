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
            if(!array_key_exists($key, $_GET)) die('key '.$key.' not exit');
            return $_GET[$key];
        }elseif($is_post){
            if(!array_key_exists($key, $_POST)) die('key '.$key.' not exit');
            return $_POST[$key];
        }elseif($is_patch){
            return $this->patch($key);
        }else{
            return $this->put($key);
        }
    }

    private function patch($key = '', $all = false){
        $_PATCH = file_get_contents('php://input');
        if($all) return $_PATCH;
        if(is_array($_PATCH) && !array_key_exists($key, $_PATCH)) die('key '.$key.' not exit');
        return empty($key) ? $_PATCH:$_PATCH[$key];
    }

    private function put($key = '', $all = false){
        $_PUT = file_get_contents('php://input');
        if($all) return $_PUT;
        if(is_array($_PUT) && !array_key_exists($key, $_PUT)) die('key '.$key.' not exit');
        return empty($key) ? $_PUT:$_PUT[$key];
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
}