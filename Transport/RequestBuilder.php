<?php

namespace Hola\Transport;

use Hola\Transport\Interface\IHeaders;

class RequestBuilder {
    protected $headers = [];

    public function requestDataGet() {
        if (!empty($_GET)) {
            return $_GET;
        }
        return [];
    }

    public function requestDataPost() {
        if (!empty($_POST)) {
            return $_POST;
        }
        return [];
    }

    public function requestDataInput() {
        $input = file_get_contents('php://input');
        if (is_string($input)) {
            $input = json_decode($input, true);
            return $input;
        }
        return $input;
    }

    public function requestDataAll() {
        $data = [];

        if (!empty($_GET)) {
            $data = array_merge($data, $_GET);
        }

        if (!empty($_POST)) {
            $data = array_merge($data, $_POST);
        }

        $input = file_get_contents('php://input');
        if (is_string($input) && !empty($input)) {
            $decodedInput = json_decode($input, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data = array_merge($data, $decodedInput);
            }
        }

        return $data;
    }

    public function requestData($method) {
        switch ($method) {
            case 'GET':
                return $this->requestDataGet();
            case 'POST':
                return $this->requestDataPost();
            case 'PATCH':
                return $this->requestDataInput();
            case 'PUT':
                return $this->requestDataInput();
            case 'DELETE':
                return $this->requestDataInput();
            case 'INPUT':
                return $this->requestDataInput();
            default:
                return [];
        }
    }
    
    public function requestGtHeader($key = '', $default = null)
    {
        $this->headers = !function_exists('getallheaders') ? [] : getallheaders();
        if (!empty($key)) {
            return $this->headers[$key] ?? $default;
        }
        return new class implements IHeaders {
             public function set($key, $value) {
                 $this->headers[$key] = $value;
                 return $this;
             }
             
             public function get($key, $default = null) {
                return $this->headers[$key] ?? $default;
             }
             
             public function has($key) {
                return isset($this->headers[$key]);
             }
             
             public function remove($key) {
                unset($this->headers[$key]);
                return $this;
             }
             
             public function all() {
                return $this->headers;
             }
             
             public function clear() {
                $this->headers = [];
                return $this;
             }
        };
    }
}