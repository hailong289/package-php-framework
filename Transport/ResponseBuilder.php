<?php

namespace Hola\Transport;
use Hola\Views\ViewRender;
use Hola\Data\Collection;
use Hola\Data\ShareData;
use Hola\Exceptions\AppException;

class ResponseBuilder {
    
    public $bindings = [
        "headers" => [],
        "status" => null,
        "action" => null,
        "data" => [],
        "metaTags" => null,
        "path" => null
    ];

    public function redirect($path){
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

    public function raw()
    {
        $rawData = '';
        if ($this->bindings['action'] == 'json') {
            $rawData = json_encode($this->bindings['data']);
        } elseif ($this->bindings['action'] == 'view') {
            $rawData = ViewRender::render($this->bindings['path'], $this->bindings['data']);
        } else if ($this->bindings['action'] == 'text') {
            $rawData = $this->bindings['data']['text'] ?? '';
        } else if ($this->bindings['action'] == 'xml') {
            $rawData = ViewRender::renderXml($this->bindings['data'])->asXML();
        } else if ($this->bindings['action'] == 'file' || $this->bindings['action'] == 'download') {
            if (is_file($this->bindings['path'])) {
                $rawData = file_get_contents($this->bindings['path']);
            } else {
                $rawData = '';
            }
        }
        return $rawData;
    }

    public function metaTag($data = [], $off = false) {
        $metaTags = [];
        
        if (!empty($data['title'])) {
            $metaTags[] = "<title>{$data['title']}</title>";
        }
        
        if (!empty($data['description'])) {
            $metaTags[] = "<meta name=\"description\" content=\"{$data['description']}\">";
        }
        
        if (!empty($data['keywords'])) {
            $metaTags[] = "<meta name=\"keywords\" content=\"{$data['keywords']}\">";
        }
        
        if (!empty($data['robots'])) {
            $metaTags[] = "<meta name=\"robots\" content=\"{$data['robots']}\">";
        }
        
        if (!empty($data['canonical'])) {
            $metaTags[] = "<link rel=\"canonical\" href=\"{$data['canonical']}\">";
        }
        
        if (!empty($data['og'])) {
            foreach ($data['og'] as $property => $content) {
                $metaTags[] = "<meta property=\"og:{$property}\" content=\"{$content}\">";
            }
        }
        
        if (!empty($data['twitter'])) {
            foreach ($data['twitter'] as $name => $content) {
                $metaTags[] = "<meta name=\"twitter:{$name}\" content=\"{$content}\">";
            }
        }

        if (!empty($data['viewport'])) {
            $metaTags[] = "<meta name=\"viewport\" content=\"{$data['viewport']}\">";
        }
        
        if (!empty($data['favicon'])) {
            $metaTags[] = "<link rel=\"icon\" href=\"{$data['favicon']}\" type=\"image/x-icon\">";
        }
        
        if (!empty($data['schema'])) {
            $jsonLD = json_encode($data['schema'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $metaTags[] = "<script type=\"application/ld+json\">{$jsonLD}</script>";
        }

        $this->bindings['metaTags'] = !$off ? implode("\n", $metaTags) : null;
        return $this;
    }

    public function xml($data = [])
    {
        $this->bindings['data'] = $data;
        $this->bindings['action'] = 'xml';
        $this->bindings['headers'] = array_merge($this->bindings['headers'], [
            'Content-Type' => 'application/xml; charset=utf-8'
        ]);
        return $this;
    }

    public function file($filePath)
    {
        $this->bindings['path'] = $filePath;
        $this->bindings['action'] = 'file';
        $this->bindings['headers'] = array_merge($this->bindings['headers'], [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
        ]);
        return $this;
    }

    public function download($filePath)
    {
        $this->bindings['path'] = $filePath;
        $this->bindings['action'] = 'download';
        $this->bindings['headers'] = array_merge($this->bindings['headers'], [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . basename($filePath) . '"'
        ]);
        return $this;
    }

    public function text($string)
    {
        $this->bindings['data'] = ['text' => $string];
        $this->bindings['action'] = 'text';
        $this->bindings['headers'] = array_merge($this->bindings['headers'], [
            'Content-Type' => 'text/plain; charset=utf-8'
        ]);
        return $this;
    }
    
    public function noContent()
    {
        $this->bindings['status'] = 204;
        $this->bindings['action'] = 'no_content';
        $this->bindings['headers'] = array_merge($this->bindings['headers'], [
            'Content-Type' => 'text/plain; charset=utf-8'
        ]);
        return $this;
    }

    public function terminate() {
        exit();
    }

    public function setStatus($code) {
        $this->bindings['status'] = $code;
        return $this;
    }

    public function setHeaders($headers = [])
    {
        if (empty($this->bindings['headers'])) {
            $this->bindings['headers'] = $headers;
        } else {
            $this->bindings['headers'] = array_merge($this->bindings['headers'], $headers);
        }
        return $this;
    }

    private function resolveHeaders()
    {

        if (empty($this->bindings['headers'])) {
            return;
        }

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
        try {
            if ($this->bindings['status'] !== null) {
                http_response_code($this->bindings['status']);
            } else {
                $this->bindings['status'] = 200;
                $this->resolveStatus();
                return;
            }
        } catch (\Throwable $e) {
            $this->bindings['status'] = 500;
            http_response_code($this->bindings['status']);
        }
    }

    private function resolveDataCollect(&$data, $retry = false) {
        if ($data instanceof Collection) {
            return $retry ? $data->data : ['items' => $data->data];
        }

        if (is_array($data)) {
            foreach ($data as &$value) {
                $value = $this->resolveDataCollect($value, true);
            }
        } elseif (is_object($data) && !($data instanceof Collection)) {
            foreach ($data as $key => &$value) {
                $value = $this->resolveDataCollect($value, true);
            }
        }

        return $data;
    }

    private function resolveMetaTags()
    {
        if (!is_null($this->bindings['metaTags'])) {
            if ($this->bindings['data'] instanceof Collection) {
                $this->bindings['data']->add($this->bindings['metaTags'], 'metaTags');
            } else if (is_array($this->bindings['data'])) {
                $this->bindings['data'] = array_merge($this->bindings['data'], [
                    "metaTags" => $this->bindings['metaTags']
                ]);
            }
        }
    }

    private function getData($share = false, $onlyData = false)
    {
        if ($onlyData) {
            return $this->bindings['data'];
        }
        $data = $this->resolveDataCollect($this->bindings['data']);
        if ($share) {
            ShareData::init()->create('data', $data);
        }
        return $data;
    }

    private function clearBindings()
    {
        $this->bindings = [
            "headers" => [],
            "status" => null,
            "action" => null,
            "data" => [],
            "metaTags" => null,
            "path" => null
        ];
        return $this;
    }
    
    public function send() {
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
                $this->terminate();
                break;
            case 'json':
                $json = json_encode($this->getData(true), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new AppException("JSON response encoding error: " . json_last_error_msg());
                }
                echo $json;
                break;
            case 'view':
                $this->resolveMetaTags();
                $html = ViewRender::render($this->bindings['path'], $this->getData(true));
                echo $html;
                break;
            case 'xml':
                $return = ViewRender::renderXml($this->getData(false, true));
                echo $return->asXML();
                break;
            case 'file':
                if (is_file($this->bindings['path'])) {
                    readfile($this->bindings['path']);
                } else {
                    throw new AppException("File not found: " . $this->bindings['path'], 404);
                }
                break;
            case 'download':
                if (is_file($this->bindings['path'])) {
                    readfile($this->bindings['path']);
                } else {
                    throw new AppException("File not found: " . $this->bindings['path'], 404);
                }
                break;
            case 'text':
                echo $this->getData(false, true)['text'] ?? '';
                break;
            default:
                break;
        }
        app()->event()->trigger('app.response', [
            'type' => 'event_response',
            'response' => $this->bindings
        ]);
        return $this->clearBindings();
    }
}