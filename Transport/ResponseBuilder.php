<?php

namespace Hola\Transport;
use Hola\Core\ViewRender;
use Hola\Data\Collection;
use Hola\Data\ShareData;
use Hola\Exceptions\AppException;

class ResponseBuilder {
    
    public $bindings = [
        "headers" => [],
        "status" => null,
        "action" => null,
        "data" => [],
        "path" => null
    ];

    public function redirectTo($path){
        $this->bindings['path'] = $path;
        $this->bindings['action'] = 'redirect';
        return $this;
    }

    public function json($data = []){
        $this->bindings['data'] = $data;
        $this->bindings['action'] = 'json';
        $this->bindings['headers'] = array_merge($this->bindings['headers'], [
            'Content-Type' => 'application/json; charset=utf-8'
        ]);
        return $this;
    }

    public function view($view, $data = []){
        $this->bindings['path'] = $view;
        $this->bindings['data'] = $data;
        $this->bindings['action'] = 'view';
        $this->bindings['headers'] = array_merge($this->bindings['headers'], [
            'Content-Type' => 'text/html; charset=utf-8'
        ]);
        return $this;
    }

    public function xmlFromData($data = [])
    {
        $this->bindings['data'] = $data;
        $this->bindings['action'] = 'xml';
        $this->bindings['headers'] = array_merge($this->bindings['headers'], [
            'Content-Type' => 'application/xml; charset=utf-8'
        ]);
        return $this;
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

    public function next(Request $request){
        $this->bindings['action'] = 'middleware';
        $this->bindings['data'] = [
            concat('', 'passable', PROJECT_KEY) => true,
            "string" => null,
            "request" => $request
        ];
        return $this;
    }

    public function close($string = ''){
        $this->bindings['action'] = 'middleware';
        $this->bindings['data'] = [
            concat('', 'passable', PROJECT_KEY) => false,
            "string" => $string,
            "request" => null
        ];
        return $this;
    }

    public function setStatus($code) {
        $this->bindings['status'] = $code;
        return $this;
    }

    public function setHeaders($headers = [])
    {
        $this->bindings['headers'] = $headers;
        return $this;
    }

    private function resolveHeaders()
    {
        foreach ($this->bindings['headers'] as $key => $value) {
            if (is_numeric($key)) {
                header($value);
            } else {
                header("$key: $value");
            }
        }
    }

    private function resolveStatus()
    {
        if ($this->bindings['status'] !== null) {
            http_response_code($this->bindings['status']);
        } else {
            http_response_code(200);
        }
    }

    private function resolveDataCollect(&$data){
        if ($data instanceof Collection) {
            $data = $data->data;
            return $this;
        }
        foreach ($data as $key => $value) {
            if ($value instanceof Collection) {
                $data[$key] = $value->data;
            } else if (is_array($value)) {
                $this->resolveDataCollect($value);
            }
        }
        return $this;
    }
    
    public function callback() {
        $this->resolveHeaders();
        if ($this->bindings['action'] !== 'redirect') {
            $this->resolveStatus();
        }
        switch ($this->bindings['action']) {
            case 'redirect':
                if (ob_get_length()) {
                    ob_end_clean();
                }
                $status = is_null($this->bindings['status']) ? 302 : $this->bindings['status'];
                header('Location: ' . $this->bindings['path'], true, $status);
                exit();
                break;
            case 'json':
                $this->resolveDataCollect($this->bindings['data']);
                ShareData::init()->create('data', $this->bindings['data']);
                $json = json_encode($this->bindings['data'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new AppException("JSON response encoding error: " . json_last_error_msg());
                }
                echo $json;
                break;
            case 'view':
                $this->resolveDataCollect($this->bindings['data']);
                ShareData::init()->create('data', $this->bindings['data']);
                echo ViewRender::render($this->bindings['path'], $this->bindings['data']);
                break;
            case 'xml':
                $return = ViewRender::renderXml($this->bindings['data']);
                echo $return->asXML();
                break;
            case 'middleware':
                return $this->bindings['data'];
                break;
            default:
                break;
        }
        return $this;
    }
}