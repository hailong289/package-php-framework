<?php
namespace Hola\Data;

class ShareData {
    public static $instance = null;
    public static $bindings = [];
    
    public static function init() {
        if (empty(defined('PROJECT_KEY') && constant('PROJECT_KEY'))) {
            die('PROJECT_KEY is not defined');
        }
        if (self::$instance == null) {
            self::$instance = new ShareData();
        }
        return self::$instance;
    }
    
    public function create($key = 'data', $value) {
        self::$bindings[PROJECT_KEY][$key] = $value;
    }

    public function all() {
        return self::$bindings[PROJECT_KEY];
    }

    public function getErrorByKey($key) {
        return self::$bindings[PROJECT_KEY]['errors'][$key] ?? null;
    }

    public function setErrors($key, $value)
    {
        self::$bindings[PROJECT_KEY]['errors'][$key] = $value;
        return $this;
    }

    public function get($key, $default = null) {
        return self::$bindings[PROJECT_KEY]['data'][$key] ?? $default;
    }

    public function set($key, $value) {
        self::$bindings[PROJECT_KEY]['data'][$key] = $value;
        return $this;
    }
    
}