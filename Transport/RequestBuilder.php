<?php

namespace Hola\Transport;

class RequestBuilder {

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
}