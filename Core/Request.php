<?php
namespace Hola\Core;
class Request extends \stdClass {
    private $file = '';

    public function __construct()
    {
        $all_data = (array)$this->all();
        if(count($all_data)) {
            foreach ($all_data as $key=>$item) {
                if(!isset($this->{$key})) $this->{$key} = $item;
            }
        }
    }

    public static function instance()
    {
        return new Request();
    }
    
    public function get($key = '', $default = null){
        $method = $_SERVER['REQUEST_METHOD'];
        $is_get = $method == 'GET' ? true:false;
        $is_post = $method == 'POST' ? true:false;
        $is_patch = $method == 'PATCH' ? true:false;
        $is_put = $method == 'PUT' ? true:false;
        if($is_get){
            return $this->get_data($key, false, $default);
        }elseif($is_post){
            return $this->post($key, false, $default);
        }elseif($is_patch){
            return $this->patch($key, false, $default);
        }else{
            return $this->put($key, false, $default);
        }
    }

    public function value($key = '', $default = null) {
        $data = file_get_contents('php://input');
        if (is_string($data)) {
            $data = json_decode($data, true);
            return $data[$key] ?? $default;
        }
        return $data[$key] ?? $default;
    }

    private function get_data($key = '', $all = false, $default = null){
        if(!empty($_GET)) {
            if($all) return $_GET;
            return $_GET[$key] ?? $default;
        } else {
            $data = file_get_contents('php://input');
            if (is_string($data)) {
                $data = json_decode($data, true);
                if($all) return $data;
                return $data[$key] ?? $default;
            }
            if($all) return $data;
            return $data[$key] ?? $default;
        }
    }

    private function post($key = '', $all = false, $default = null){
        if(!empty($_POST)) {
            if($all) return $_POST;
            return $_POST[$key] ?? $default;
        } else {
            $data = file_get_contents('php://input');
            if (is_string($data)) {
                $data = json_decode($data, true);
                if($all) return $data;
                return $data[$key] ?? $default;
            }
            if($all) return $data;
            return $data[$key] ?? $default;
        }
    }

    private function patch($key = '', $all = false, $default = false){
        $_PATCH = file_get_contents('php://input');
        if($all) return $_PATCH;
        return $_PATCH[$key] ?? $default;
    }

    private function put($key = '', $all = false, $default = null){
        $_PUT = file_get_contents('php://input');
        if($all) return $_PUT;
        return $_PUT[$key] ?? $default;
    }

    public function file($key = ''){
        $this->file = $_FILES[$key] ?? '';
        return $this;
    }
    public function get_file($key = ''){
        $this->file = $_FILES[$key] ?? '';
        return $this->file;
    }

    public function tmp_name(){
        if(empty($this->file)) {
            throw new \Exception('File not set');
        }
        return $this->file['tmp_name'];
    }

    public function size(){
        if(empty($this->file)) {
            throw new \Exception('File not set');
        }
        return $this->file['size'];
    }

    public function type(){
        if(empty($this->file)) {
            throw new \Exception('File not set');
        }
        return $this->file['type'];
    }

    public function error(){
        if(empty($this->file)) {
            throw new \Exception('File not set');
        }
        return $this->file['error'];
    }

    public function originName(){
        if(empty($this->file)) {
            throw new \Exception('File not set');
        }
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
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
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
        return [];
    }

    public function session($key = ''){
        return $_SESSION[$key] ?? null;
    }

    public function cookie($key = ''){
        return $_COOKIE[$key] ?? null;
    }

    public function headers($key){
        $headers = !function_exists('getallheaders') ? [] : getallheaders();
        return $headers[$key] ?? null;
    }

    public function isJson(){
        $accept = $this->headers('Accept');
        return strpos($accept,'application/json') !== false;
    }
    
    public function next($string = ''){
        return [
            "message" => $string,
            "error_code" => 0
        ];
    }

    public function close($string = ''){
        return [
            "message" => $string,
            "error_code" => 1
        ];
    }
    public function has($key){
        return $this->get($key) ?? false;
    }
}