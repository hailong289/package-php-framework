<?php

namespace Hola\Data;

use Hola\Connection\Redis;
use Hola\Exceptions\AppException;

class Cache {
    public static Cache|null $instance = null;
    private static $bind = null;
    private $default_connection = null;
    private $prefix = 'cache_';
    private $path = __DIR__ROOT . '/storage/cache';

    public function __construct() {
        $this->bind = config('cache.default');
        $this->default_connection = config('cache.default_connections');
        $this->prefix = config('cache.prefix') ?? 'cache_';
        $this->path = __DIR__ROOT . '/' . config("cache.stores.{$this->bind}.path");
    }

    public function redis($name = null) {
        $this->bind = 'redis';
        if (!is_null($name)) {
            $this->default_connection = $name;
        }
        return $this;
    }

    public function file() {
        $this->bind = 'file';
        return $this;
    }

    public function apc() {
        if (!function_exists('apcu_store')) {
            throw new AppException('APCu is not enabled.');
        }
        $this->bind = 'apc';
        return $this;
    }
    
    public function setPath($path) {
        $this->path = __DIR__ROOT . '/' . $path;
        return $this;
    }
    
    public function setPrefix($prefix) {
        $this->prefix = $prefix;
        return $this;
    }

    public function get($name) {
        $name = $this->prefix . $name;
        switch ($this->bind) {
            case 'file':
                return $this->getDataFile($name, $callback);
                break;
            case 'redis':
                return $this->getDataRedis($name, $callback);
                break;
           case 'apc':
                return $this->getDataApc($name, $callback);
                break;
            default:
                throw new AppException('Cache driver do not support');
                break;
        }
    }

    public function store($name, $data = [], $time = null) {
        $name = $this->prefix . $name;
        switch ($this->bind) {
            case 'file':
                $this->storeFile($name, $data, $time);
                break;
            case 'redis':
                $this->storeRedis($name, $data, $time);
                break;
            case 'apc':
                $this->storeApc($name, $data, $time);
                break;
            default:
                throw new AppException('Cache driver do not support');
                break;
        }
        return $this;
    }

    public function getOrStore($name, $data = [], $time = null)
    {
        if (empty($this->get($name))) {
            $this->store($name, $data, $time);
            return $data;
        }
        return $this->get($name);
    }

    private function getDataRedis($name) {
        $data_cache = $this->getRedis()->get($name);
        if (!empty($data_cache)) {
            return json_decode($data_cache, true);
        }
        return [];
    }

    private function getDataFile($name) {
        $data_cache = file_get_contents("$this->path/$name.cache");
        if (!empty($data_cache)) {
            return unserialize($data_cache);
        }
        return [];
    }

    private function getDataApc($name) {
        $data_cache = apc_fetch($name);
        if (!empty($data_cache)) {
            return unserialize($data_cache);
        }
        return [];
    }

    private function storeRedis($tags, $data = [], $time = null) {
        if (is_null($time)) {
            $time = config("cache.stores.{$this->bind}.expire");
        }
        $this->getRedis()->setex($tags, $time, json_encode($data));
    }

    private function storeFile($name, $data = [], $time = null) {
        if (is_null($time)) {
            $time = config("cache.stores.{$this->bind}.expire");
        }
        createFolder($this->path);
        $cacheFile = $this->getLinkFile($name);
        if (file_exists($cacheFile)) {
            $effect = (time() - filemtime($cacheFile) < $time);
            if (!$effect) {
                file_put_contents($cacheFile, serialize($data));
            }
        } else {
            file_put_contents($cacheFile, serialize($data));
        }
    }

    private function storeApc($name, $data = [], $time = null) {
        if (is_null($time)) {
            $time = config("cache.stores.{$this->bind}.expire");
        }
        apc_store($name, serialize($data), $time);
    }

    public function clear($name)
    {
        $name = $this->prefix . $name;
        switch($this->bind) {
            case 'file':
                $this->clearFile($name);
                break;
            case 'redis':
                $this->clearRedis($name);
                break;
            case 'apc':
                $this->clearApc($name);
                break;
            default:
                throw new AppException('Cache driver do not support');
                break;
        }
    }

    private function clearFile($name)
    {
        $cacheFile = $this->getLinkFile($name);
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    private function clearRedis($name)
    {
        $this->getRedis()->del($name);
    }

    private function clearApc($name)
    {
        apc_delete($name);
    }

    private function getLinkFile($name) {
        return "$this->path/$name.cache";
    }

    private function getRedis(): \Redis {
        $redis = Redis::instance($this->default_connection);
        return $redis;
    }

}