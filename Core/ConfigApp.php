<?php

namespace Hola\Core;

class ConfigApp {
    public static $config = null;
    private static $instance = null;
    private static $bindings = [];

    public static function init() {
        if (self::$instance == null) {
            self::$instance = new ConfigApp();
        }
        return self::$instance;
    }

    public function create($key, $value) {
        if (empty(constant('PROJECT_KEY'))) {
           die('PROJECT_KEY is not defined');
        }
        self::$bindings[PROJECT_KEY][$key] = $value;
    }

    public function all()
    {
        return self::$bindings[PROJECT_KEY];
    }

    public function get($name) {
        $list_config = self::$bindings[PROJECT_KEY] ?? [];
        $keys = explode('.', $name);
        $firstName = array_shift($keys);
        $config = $list_config[$firstName] ?? [];
        return findDataByKeys($keys, $config);
    }

    public function set($name, $value) {
        $list_config = self::$bindings[PROJECT_KEY] ?? [];
        $keys = explode('.', $name);
        $firstName = array_shift($keys);
        $config = $list_config[$firstName] ?? [];
        updateDataByKeys($config, $keys, $value);
        $this->create($firstName, $config);
        return $this;
    }
}