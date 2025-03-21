<?php
namespace Hola\Data\Cache\Driver;
use Hola\Data\Cache\Interfaces\ICacheDriver;

class CacheFile implements ICacheDriver {
    private $prefix = 'cache_';
    private $path = __DIR__ROOT . '/storage/cache';
    private $expire = 3600;

    public function __construct() {
        $this->prefix = config('cache.prefix') ?? 'cache_';
        $this->path = __DIR__ROOT . '/' . config("cache.stores.file.path");
    }

    public function prefix($prefix) {
        $this->prefix = $prefix;
        return $this;
    }

    public function path($path) {
        $this->path = $path;
        return $this;
    }

    public function get($key) {
        $key = $this->prefix . $key;
        if (!$this->has($key)) {
            return [];
        }
        $cacheFile = $this->getLinkFile($key);
        $effect = (time() - filemtime($cacheFile) < $time);
        if (!$effect) { // expired
            $this->delete($key);
            return [];
        }
        $values = file_get_contents($cacheFile);
        return unserialize($values);
    }

    public function set($key, $values = [], $time = null) {
        $key = $this->prefix . $key;
        createFolder($this->path);
        $time = $time ?? $this->expire;
        if (!$this->has($key)) {
            return file_put_contents($this->getLinkFile($key), serialize($values));
        }
        $cacheFile = $this->getLinkFile($key);
        $effect = (time() - filemtime($cacheFile) < $time);
        if (!$effect) {
            return file_put_contents($cacheFile, serialize($values));
        }
        return true;
    }

    public function delete($key) {
        $key = $this->prefix . $key;
        $cacheFile = $this->getLinkFile($key);
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    public function clear() {
        $cache = rglob("$this->path/*.cache");
        if (!empty($cache)) {
            foreach($cache as $item){
                if(file_exists($item)){
                    unlink($item);
                }
            }
        }
    }

    public function has($key) {
        $key = $this->prefix . $key;
        $cacheFile = $this->getLinkFile($key);
        return file_exists($cacheFile);
    }

    private function getLinkFile($name) {
        return "$this->path/$name.cache";
    }
}