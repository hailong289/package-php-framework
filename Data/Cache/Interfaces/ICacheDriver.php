<?php
namespace Hola\Data\Cache\Interfaces;

interface ICacheDriver {
    public function prefix($prefix);
    public function get($key);
    public function set($key, $values = [], $time = null);
    public function delete($key);
    public function clear();
    public function has($key);
}