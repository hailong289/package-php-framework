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
        "metaTags" => null,
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

    public function metaTag($data = []) {
        $metaTags = [];

        // Title
        if (!empty($data['title'])) {
            $metaTags[] = "<title>{$data['title']}</title>";
        }

        // Meta Description
        if (!empty($data['description'])) {
            $metaTags[] = "<meta name=\"description\" content=\"{$data['description']}\">";
        }

        // Meta Keywords
        if (!empty($data['keywords'])) {
            $metaTags[] = '<meta name="keywords" content="' . htmlspecialchars(implode(", ", $data['keywords'])) . '">';
        }

        // Meta Robots
        if (!empty($data['robots'])) {
            $metaTags[] = "<meta name=\"robots\" content=\"{$data['robots']}\">";
        }

        // Canonical
        if (!empty($data['canonical'])) {
            $metaTags[] = "<link rel=\"canonical\" href=\"{$data['canonical']}\">";
        }

        // Open Graph
        if (!empty($data['og'])) {
            foreach ($data['og'] as $property => $content) {
                $metaTags[] = "<meta property=\"og:{$property}\" content=\"{$content}\">";
            }
        }

        // Twitter Card
        if (!empty($data['twitter'])) {
            foreach ($data['twitter'] as $name => $content) {
                $metaTags[] = "<meta name=\"twitter:{$name}\" content=\"{$content}\">";
            }
        }

        if (!empty($data['viewport'])) {
            $metaTags[] = "<meta name=\"viewport\" content=\"{$data['viewport']}\">";
        }

        // Favicon
        if (!empty($data['favicon'])) {
            $metaTags[] = "<link rel=\"icon\" href=\"{$data['favicon']}\" type=\"image/x-icon\">";
        }

        // Structured Data (Schema.org)
        if (!empty($data['schema'])) {
            $jsonLD = json_encode($data['schema'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $metaTags[] = "<script type=\"application/ld+json\">{$jsonLD}</script>";
        }

        $this->bindings['metaTags'] = implode("\n", $metaTags);
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

    public function exit() {
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
        try {
            if ($this->bindings['status'] !== null) {
                http_response_code($this->bindings['status']);
            } else {
                http_response_code(200);
            }
        } catch (\Throwable $e) {
            http_response_code(500);
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
    
    public function callback($output = null) {
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
                $this->exit();
                break;
            case 'json':
                $json = json_encode($this->getData(true), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new AppException("JSON response encoding error: " . json_last_error_msg());
                }
                if ($output) {
                    return $json;
                }
                echo $json;
                break;
            case 'view':
                $this->resolveMetaTags();
                $html = ViewRender::render($this->bindings['path'], $this->getData(true));
                if ($output) {
                    return $html;
                }
                echo $html;
                break;
            case 'xml':
                $return = ViewRender::renderXml($this->getData(false, true));
                if ($output) {
                    return $return->asXML();
                }
                echo $return->asXML();
                break;
            case 'middleware':
                return $this->getData(false, true);
                break;
            default:
                break;
        }
        return $this->clearBindings();
    }
}