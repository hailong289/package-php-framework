<?php
namespace Hola\Data\Cache;
use Hola\Data\Cache\Driver\CacheApc;
use Hola\Data\Cache\Driver\CacheFile;
use Hola\Data\Cache\Driver\CacheRedis;
use Hola\Exceptions\AppException;

class CacheManager {
    private $bind = null;
    public CacheFile|CacheRedis|CacheApc|null $driver = null;

    public function __construct() {
        $this->bind = config('cache.default');
    }

    private function driver()
    {
        switch ($this->bind) {
            case 'file':
                $this->driver = new CacheFile();
                break;
            case 'redis':
                $this->driver = new CacheRedis();
                break;
            case 'apc':
                $this->driver = new CacheApc();
                break;
            default:
                throw new AppException('Cache driver do not support');
                break;
        }
        return $this;
    }

    public function getDriver() {
        return $this->driver;
    }

    public function redis($name = null) {
        $this->bind = 'redis';
        return $this->driver();
    }

    public function file() {
        $this->bind = 'file';
        return $this->driver();
    }

    public function apc() {
        if (!function_exists('apcu_store')) {
            throw new AppException('APCu is not enabled.');
        }
        $this->bind = 'apc';
        return $this;
    }

    public function setPrefix($prefix) {
        $this->getDriver()->prefix($prefix);
        return $this;
    }

    public function setPath($path) {
        if (!$this->getDriver() instanceof CacheFile) {
            throw new AppException('Cache driver do not support method setPath');
        }
        $this->getDriver()->path($path);
        return $this;
    }

    public function get($key)
    {
        return $this->getDriver()->get($key);
    }

    public function store($key, $values = [], $time = null)
    {
        return $this->getDriver()->set($key, $values, $time);
    }

    public function delete($key) {
        return $this->getDriver()->delete($key);
    }

    public function clear() {
        return $this->getDriver()->clear();
    }

    public function has($key) {
        return $this->getDriver()->has($key);
    }

    public function getOrStore($key, $values = [], $time = null)
    {
        if (empty($values)) {
            return [];
        }
        if (empty($this->get($key))) {
            $this->store($key, $values, $time);
            return $values;
        }
        return $this->get($key);
    }

}