<?php

namespace Hola\Data\Cache\Driver;

use Hola\Data\Cache\Interfaces\ICacheDriver;
use Hola\Exceptions\AppException;

class CacheApc implements ICacheDriver {
    public $instance;
    private $prefix = 'cache_';
    private $expire = 3600;
    public function __construct() {
        if (!function_exists('apcu_store')) {
            throw new AppException('APCu is not enabled.');
        }
    }

    public function prefix($prefix) {
        $this->prefix = $prefix;
        return $this;
    }

    public function get($key) {
        $key = $this->prefix . $key;
        $data_cache = apc_fetch($key);
        if (!empty($data_cache)) {
            return unserialize($data_cache);
        }
        return [];
    }

    public function set($key, $values = [], $time = null) {
        $key = $this->prefix . $key;
        return apc_store($key, serialize($values), $time ?? $this->expire);
    }

    public function delete($key) {
        $key = $this->prefix . $key;
        return apc_delete($key);
    }

    public function clear() {
        return apc_clear_cache();
    }

    public function has($key) {
        $key = $this->prefix . $key;
        return apc_exists($key);
    }
}