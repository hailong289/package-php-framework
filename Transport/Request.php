<?php
namespace Hola\Transport;

use Hola\Data\Cookie;
use Hola\Data\Session;
use Hola\Exceptions\AppException;

class Request extends RequestBuilder {
    protected array $attributes = [];
    private $file = '';

    public function __construct()
    {
        $all_data = (array)$this->all();
        foreach ($all_data as $key => $item) {
            $this->attributes[$key] = $item;
        }
    }

    public function __get($key) {
        return $this->attributes[$key] ?? null;
    }

    public function get($key = '', $default = null)
    {
        $data = $this->requestData('GET');
        return $data[$key] ?? $default;
    }

    public function value($key = '', $default = null)
    {
        $data = $this->requestData('INPUT');
        return $data[$key] ?? $default;
    }

    public function post($key = '', $default = null)
    {
        $data = $this->requestData('POST');
        return $data[$key] ?? $default;
    }

    public function patch($key = '', $default = false)
    {
        $_PATCH = $this->requestData('PATCH');
        return $_PATCH[$key] ?? $default;
    }

    public function put($key = '', $default = null)
    {
        $_PUT = $this->requestData('PUT');
        return $_PUT[$key] ?? $default;
    }

    public function file($key = '')
    {
        $this->file = $_FILES[$key] ?? '';
        return $this;
    }

    public function getFile($key = '')
    {
        $this->file = $_FILES[$key] ?? '';
        return $this->file;
    }

    public function tmpName()
    {
        if (empty($this->file)) {
            throw new AppException('File not set');
        }
        return $this->file['tmp_name'];
    }

    public function size()
    {
        if (empty($this->file)) {
            throw new AppException('File not set');
        }
        return $this->file['size'];
    }

    public function type()
    {
        if (empty($this->file)) {
            throw new AppException('File not set');
        }
        return $this->file['type'];
    }

    public function errorFile()
    {
        if (empty($this->file)) {
            throw new AppException('File not set');
        }
        return $this->file['error'];
    }

    public function originName()
    {
        if (empty($this->file)) {
            throw new AppException('File not set');
        }
        return current((explode(".", $this->file['name'])));
    }

    public function extension()
    {
        if (empty($this->file)) die('key not exit');
        $array_file = explode(".", $this->file['name']);
        return end($array_file);
    }

    public function isFile($key = '')
    {
        if (!file_exists($_FILES[$key]['tmp_name'])) {
            return false;
        }
        return true;
    }

    public function all()
    {
        $data = $this->requestDataAll();
        return $data;
    }

    public function session($key = '', $default = null)
    {
        if (empty($key)) {
            return new Session();
        }
        return $_SESSION[$key] ?? $default;
    }

    public function cookie($key = '', $default = null)
    {
        if (empty($key)) {
            return new Cookie();
        }
        return $_COOKIE[$key] ?? $default;
    }

    public function headers($key = '', $default = null)
    {
        return $this->requestGetHeader($key, $default);
    }

    public function isJson()
    {
        $accept = $this->headers('Accept') ?? '';
        return strpos($accept, 'application/json') !== false;
    }

    public function set($name, $value = null)
    {
        $this->{$name} = $value;
        return $this;
    }

    public function has($key)
    {
        $data = $this->all();
        return isset($data[$key]) ? true : false;
    }

    public function any($name)
    {
        $data = $this->all();
        return $data[$name] ?? null;
    }

    public function domain() {
        return $_SERVER['HTTP_HOST'];
    }

    public function domainName() {
        return $_SERVER['SERVER_NAME'];
    }

    public function originalDomain()
    {
        return $_SERVER['HTTP_ORIGIN'] ?? '';
    }

    public function path() {
        $request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return $request_path;
    }

    public function hasPath($path) {
        return $this->path() === $path;
    }

    public function method() {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function isMethod($method) {
        return $this->method() === strtoupper($method);
    }

    public function isGet() {
        return $this->isMethod('GET');
    }

    public function isPost() {
        return $this->isMethod('POST');
    }

    public function isPut() {
        return $this->isMethod('PUT');
    }

    public function isPatch() {
        return $this->isMethod('PATCH');
    }

    public function isDelete() {
        return $this->isMethod('DELETE');
    }

    public function isOptions() {
        return $this->isMethod('OPTIONS');
    }

    public function isHead() {
        return $this->isMethod('HEAD');
    }

    public function isAjax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    public function isSecure() {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    }

    public function isXmlRequest() {
        return $this->headers('Content-Type') === 'application/xml';
    }

    public function isHtmlRequest() {
        return $this->headers('Content-Type') === 'text/html';
    }

    public function isFormRequest() {
        return $this->headers('Content-Type') === 'application/x-www-form-urlencoded';
    }

    public function isMultipartRequest() {
        return $this->headers('Content-Type') === 'multipart/form-data';
    }

    public function isJsonRequest() {
        return $this->headers('Content-Type') === 'application/json';
    }

    public function isTextRequest() {
        return $this->headers('Content-Type') === 'text/plain';
    }

    public function ip()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ip_list[0]);
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }
        return '';
    }

    public function userAgent()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown User Agent';
    }

    public function referer()
    {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Unknown Referer';
    }
}