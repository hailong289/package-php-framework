<?php
namespace Hola\Transport\Interface;
interface IHeaders {
    public function set($key, $value);
    public function get($key, $default = null);
    public function has($key);
    public function all();
    public function remove($key);
    public function clear();
}