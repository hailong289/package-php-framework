<?php

namespace Hola\Data\Cache\Driver;
use Hola\Connection\ConnectionManager;
use Hola\Connection\Redis;
use Hola\Data\Cache\Interfaces\ICacheDriver;

class CacheRedis implements ICacheDriver {
    public \Redis $instance;
    private string $prefix;
    private string|null $default_connection = null;
    private $expire = 3600;

    public function __construct() {
        $this->default_connection = config('cache.default_connections');
        $this->prefix = config('cache.prefix') ?? 'cache_';
        $this->expire = config("cache.stores.redis.expire");
        $this->connect();
    }

    public function prefix($prefix) {
        $this->prefix = $prefix;
        return $this;
    }

    public function get($key) {
        $key = $this->prefix . $key;
        $data_cache = $this->instance->get($key);
        if (!empty($data_cache)) {
            return json_decode($data_cache, true);
        }
        return [];
    }

    public function set($key, $values = [], $time = null) {
        $key = $this->prefix . $key;
        return $this->instance->setex($key, $time ?? $this->expire, json_encode($values));
    }

    public function delete($key) {
        $key = $this->prefix . $key;
        return $this->instance->del($key);
    }

    public function clear() {
        return $this->instance->flushDB();
    }

    public function has($key) {
        $key = $this->prefix . $key;
        return $this->instance->exists($key);
    }

    private function connect() {
        $redis = app()
                ->get(ConnectionManager::class)
                ->setConfigName('cache')
                ->setConnectionType('redis')
                ->setConnectionName($this->default_connection);
        $redis->handle();
        $this->instance = $redis->getConnection();
        return $this;
    }
}