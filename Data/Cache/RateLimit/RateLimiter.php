<?php

namespace Hola\Data\Cache\RateLimit;

use Hola\Data\Cache\CacheManager;

class RateLimiter {
    protected $key = '';
    protected $maxAttempts = 5;
    protected $delaySeconds = 60;
    protected $numberOfAttempts = 0;
    protected $limiter = 'file'; // 'file', 'redis'
    protected $cache = null;

    public function __construct(
        $maxAttempts = 5,
        $delaySeconds = 60,
        $limiter = 'file' // 'file', 'redis'
    ) {
        $this->maxAttempts = $maxAttempts;
        $this->delaySeconds = $delaySeconds;
        $this->limiter = $limiter;
        $this->useLimiter($limiter);
    }

    public function useLimiter($limiter) {
        $this->limiter = $limiter;
        switch ($limiter) {
            case 'file':
                $this->cache = (new CacheManager())->file()->getDriver();
                $this->cache->expire($this->delaySeconds);
                break;
            case 'redis':
                $this->cache = (new CacheManager())->redis()->getDriver();
                $this->cache->expire($this->delaySeconds);
                break;
            default:
                throw new \Exception('Unsupported limiter type: ' . $limiter);
        }
    }

    public function setKey($key) {
        $this->key = 'rate_limit:' . $key;
        return $this;
    }

    public function setMaxAttempts($maxAttempts) {
        $this->maxAttempts = $maxAttempts;
        return $this;
    }

    public function setDelaySeconds($delaySeconds) {
        $this->delaySeconds = $delaySeconds;
        return $this;
    }

    public function increment($key)
    {
        $key = 'rate_limit:' . $key;
        $this->numberOfAttempts = $this->attempts();
        if ($this->key !== $key) {
            $this->key = $key;
            $this->numberOfAttempts = 0;
        }
        $this->numberOfAttempts = $this->numberOfAttempts + 1;
        if (is_null($this->cache)) {
            throw new \Exception('Cache driver is not set. Please set a cache driver before using the rate limiter.');
        }
        $this->cache->set($this->key, $this->numberOfAttempts, $this->delaySeconds);
        return $this;
    }

    public function decrement($key) {
        $key = 'rate_limit:' . $key;
        $this->numberOfAttempts = $this->attempts();
        if ($this->key === $key) {
            if ($this->numberOfAttempts > 0) {
                $this->numberOfAttempts--;
            }
            if (is_null($this->cache)) {
                throw new \Exception('Cache driver is not set. Please set a cache driver before using the rate limiter.');
            }
            $this->cache->set($this->key, $this->numberOfAttempts, $this->delaySeconds);
        }
        return $this;
    }

    public function attempts() {
        if (is_null($this->cache)) {
            throw new \Exception('Cache driver is not set. Please set a cache driver before using the rate limiter.');
        }
        $numberOfAttempts = $this->cache->get($this->key);
        return (int)(empty($numberOfAttempts) ? 0 : $numberOfAttempts);
    }

    public function maxAttempts()
    {
        return $this->maxAttempts;
    }

    public function tooManyAttempts() {
        if ($this->attempts() >= $this->maxAttempts()) {
            return true;
        }
        return false;
    }

    public function clear() {
        $this->numberOfAttempts = 0;
        $this->cache->delete($this->key);
    }
}