<?php

namespace App\Core;

class Response {
    public function redirectTo($path, $status = 302, $headers = []){
        header('Location: ' . $path, true, $status);
        exit();
    }
    public function json($data = [], $status = 200, $headers = [], $options = 0){
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit();
    }

    public function view($view, $data = [], $status = 200){
        http_response_code($status);
        return get_view($view, $data);
    }
}