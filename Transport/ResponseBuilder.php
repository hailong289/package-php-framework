<?php

namespace Hola\Transport;
use Hola\Core\ViewRender;
use Hola\Data\Collection;
use Hola\Data\ShareData;

class ResponseBuilder {

    public function redirectTo($path, $status = 302, $headers = []){
        header('Location: ' . $path, true, $status);
        exit();
    }

    public function json($data = [], $status = 200, $headers = []){
        $headers['Content-Type'] = 'application/json; charset=utf-8';
        $this->setHeaders($headers, $status);
        $this->resloveDataCollect($data);
        ShareData::init()->create('data', $data);
        return $data;
    }

    public function view($view, $data = [], $status = 200, $headers = []){
        $headers['Content-Type'] = 'text/html; charset=utf-8';
        $this->setHeaders($headers, $status);
        $this->resloveDataCollect($data);
        ShareData::init()->create('data', $data);
        return ViewRender::render($view, $data);
    }

    public function xmlFromData($data = [], $status = 200, $headers = [])
    {
        $headers['Content-Type'] = 'application/xml; charset=utf-8';
        $this->setHeaders($headers, $status);
        return ViewRender::renderXml($data);
    }

    public function withExit($type, \Closure $callback){
        $data = $callback();
        if ($type === 'json') {
            echo self::json(...$data);
        } else if ($type === 'view') {
            self::view(...$data);
        } else if ($type === 'require_once') {
            require_once($data);
        } else if ($type === 'include') {
            include($data);
        } else if ($type === 'readfile') {
            readfile($data);
        } else {
            echo $data;
        }
        exit();
    }

    public function next(Request $request, $code = 0){
        if ($code !== 0) http_response_code($code);
        $data = [
            "pass_middleware" => 1,
            'request' => $request
        ];
        return $data;
    }

    public function close($string = '', $code = 0){
        if ($code !== 0) http_response_code($code);
        return ["message" => $string];
    }

    private function setHeaders($headers = [], $status = 200)
    {
        foreach($headers as $key => $value){
            if (is_numeric($key)) {
                header($value, true, $status);
            } else {
                header($key . ': ' . $value, true, $status);
            }
        }
    }

    private function resloveDataCollect(&$data){
        if ($data instanceof Collection) {
            $data = $data->data;
            return $this;
        }
        foreach ($data as $key => $value) {
            if ($value instanceof Collection) {
                $data[$key] = $value->data;
            } else if (is_array($value)) {
                $this->resloveDataCollect($value);
            }
        }
        return $this;
    }
}