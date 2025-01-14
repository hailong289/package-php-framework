<?php
namespace Hola\Transport;

use Hola\Exceptions\AppException;

class Request extends RequestBuilder {
    private $file = '';

    public function __construct()
    {
        $all_data = (array)$this->all();
        if (count($all_data)) {
            foreach ($all_data as $key => $item) {
                if (!isset($this->{$key})) $this->{$key} = $item;
            }
        }
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

    private function post($key = '', $default = null)
    {
        $data = $this->requestData('POST');
        return $data[$key] ?? $default;
    }

    private function patch($key = '', $default = false)
    {
        $_PATCH = $this->requestData('PATCH');
        return $_PATCH[$key] ?? $default;
    }

    private function put($key = '', $default = null)
    {
        $_PUT = $this->requestData('PUT');
        return $_PUT[$key] ?? $default;
    }

    public function file($key = '')
    {
        $this->file = $_FILES[$key] ?? '';
        return $this;
    }

    public function get_file($key = '')
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

    public function session($key = '')
    {
        return $_SESSION[$key] ?? null;
    }

    public function cookie($key = '')
    {
        return $_COOKIE[$key] ?? null;
    }

    public function headers($key)
    {
        $headers = !function_exists('getallheaders') ? [] : getallheaders();
        return $headers[$key] ?? null;
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
}