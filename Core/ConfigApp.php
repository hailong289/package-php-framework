<?php

namespace Hola\Core;

class ConfigApp {
    public static $config = null;
    private static $instance = null;
    private static $bindings = [];

    /**
     * ConfigApp constructor.
     */
    public static function init() {
        if (self::$instance == null) {
            self::$instance = new ConfigApp();
        }
        return self::$instance;
    }

    /**
     * @param $key
     * @param $value
     */
    public function create($key, $value) {
        self::$bindings[conval('PROJECT_KEY')][$key] = $value;
    }

    /**
     * @return array
     */
    public function all()
    {
        return self::$bindings[conval('PROJECT_KEY')];
    }

    /**
     * @param $name
     * @return mixed
     */
    public function get($name) {
        $list_config = self::$bindings[conval('PROJECT_KEY')] ?? [];
        $keys = explode('.', $name);
        $firstName = array_shift($keys);
        $config = $list_config[$firstName] ?? [];
        return findDataByKeys($keys, $config);
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function set($name, $value) {
        $list_config = self::$bindings[conval('PROJECT_KEY')] ?? [];
        $keys = explode('.', $name);
        $firstName = array_shift($keys);
        $config = $list_config[$firstName] ?? [];
        updateDataByKeys($config, $keys, $value);
        $this->create($firstName, $config);
        return $this;
    }
}