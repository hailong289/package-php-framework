<?php

namespace Hola\Data;

use Hola\Connection\Redis;

class Cache {
    public static $instance = null;
    public $bindings = [
        'redis' => null,
        'file' => false
    ];

    public static function init() {
        if (self::$instance == null) {
            self::$instance = new Cache();
        }
        return self::$instance;
    }

    public function get($name) {
        if ($this->bindings['file']) {
            return $this->getDataFile($name);
        }
        return $this->getDataRedis($name);
    }

    public function redis($default_connect = 'redis', $is_return = false) {
        $name = $default_connect ?? config_env('REDIS_CONNECTION','redis');
        $redis = Redis::instance($name);
        $this->bindings['redis'] = $redis;
        $this->bindings['file'] = false;
        if ($is_return) {
            return $redis;
        }
        return $this;
    }

    public function file() {
        $this->bindings['file'] = true;
        $this->bindings['redis'] = null;
        return $this;
    }

    public function store($name, $data = [], $time = 3600) {
        if ($this->bindings['file']) {
            $this->storeFile($name, $data, $time);
        } else {
            $this->storeRedis($name, $data, $time);
        }
        return $this;
    }

    public function getOrStore($name, $data = [], $time = 3600)
    {
        if ($this->bindings['file']) {
            return $this->storeFile($name, $data, $time,true);
        }
        return $this->storeRedis($name, $data, $time, true);
    }

    private function getDataRedis($name) {
        $data_cache = $this->bindings['redis']->get($name);
        if (!empty($data_cache)) {
            return unserialize($data_cache);
        }
        return [];
    }

    private function storeRedis($tags, $data = [], $time = 3600, $is_get = false) {
        $this->bindings['redis']->set($tags, serialize($data));
        $this->bindings['redis']->expire($tags, $time);
        if ($is_get) {
            return $data;
        }
    }

    private function getDataFile($name) {
        $data_cache = file_get_contents(__DIR__ROOT ."/storage/cache/$name.cache");
        if (!empty($data_cache)) {
            return unserialize($data_cache);
        }
        return [];
    }

    private function storeFile($name, $data = [], $time = 3600, $is_get = false) {
        createFolder(__DIR__ROOT .'/storage/cache');
        $cacheFile = $this->getLinkFile($name);
        if (file_exists($cacheFile)) {
            $effect = (time() - filemtime($cacheFile) < $time);
            if (!$effect) {
                file_put_contents(__DIR__ROOT ."/storage/cache/$name.cache", serialize($data));
            }
        } else {
            file_put_contents(__DIR__ROOT ."/storage/cache/$name.cache", serialize($data));
        }

        if ($is_get) {
            return $data;
        }
    }

    public function clear($key)
    {
        if ($this->bindings['file']) {
            $this->clearFile($key);
        } else {
            $this->clearRedis($key);
        }
    }

    private function clearFile($key)
    {
        $cacheFile = $this->getLinkFile($key);
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    private function clearRedis($key)
    {
        $this->bindings['redis']->del($key);
    }

    private function getLinkFile($name) {
        return __DIR__ROOT ."/storage/cache/$name.cache";
    }


}