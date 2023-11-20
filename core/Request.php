<?php
namespace App\Core;
class Request extends \stdClass {
    private $file = '';

    public function __construct()
    {
        $all_data = $this->all();
        foreach ($all_data as $key=>$item) {
            if(!isset($this->{$key})) $this->{$key} = $item;
        }
    }
    
    public function get($key = ''){
        $method = $_SERVER['REQUEST_METHOD'];
        $is_get = $method == 'GET' ? true:false;
        $is_post = $method == 'POST' ? true:false;
        $is_patch = $method == 'PATCH' ? true:false;
        $is_put = $method == 'PUT' ? true:false;
        if($is_get){
            return $this->get_data($key);
        }elseif($is_post){
            return $this->post($key);
        }elseif($is_patch){
            return $this->patch($key);
        }else{
            return $this->put($key);
        }
    }

    public function value($key = '') {
        $data = file_get_contents('php://input');
        if (is_string($data)) {
            $data = json_decode($data, true);
            return $data[$key] ?? null;
        }
        return $data[$key] ?? null;
    }

    private function get_data($key = '', $all = false){
        if(!empty($_GET)) {
            if($all) return $_GET;
            return $_GET[$key] ?? null;
        } else {
            $data = file_get_contents('php://input');
            if (is_string($data)) {
                $data = json_decode($data, true);
                if($all) return $data;
                return $data[$key] ?? null;
            }
            if($all) return $data;
            return $data[$key] ?? null;
        }
    }

    private function post($key = '', $all = false){
        if(!empty($_POST)) {
            if($all) return $_POST;
            return $_POST[$key] ?? null;
        } else {
            $data = file_get_contents('php://input');
            if (is_string($data)) {
                $data = json_decode($data, true);
                if($all) return $data;
                return $data[$key] ?? null;
            }
            if($all) return $data;
            return $data[$key] ?? null;
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
    public function get_file($key = ''){
        if(!array_key_exists($key, $_FILES)) die('key not exit');
        $this->file = $_FILES[$key];
        return  $this->file;
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
        return current((explode(".", $this->file['name'])));
    }

    public function extension(){
        if(empty($this->file)) die('key not exit');
        $array_file = explode(".", $this->file['name']);
        return end($array_file);
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
            return $this->get_data('', true);
        }elseif($is_post){
            return $this->post('', true);
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
    public function has($key){
        return $this->get($key) ?? false;
    }
}