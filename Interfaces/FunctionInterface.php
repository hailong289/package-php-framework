<?php

namespace Hola\Interfaces\FunctionInterface;

interface InterfaceErrors {
    public function get($key = '');
    public function set($key = '', $value = '');
    public function all();
}

interface InterfaceRes {
    public function view($name, $data = [], $status = 200);
    public function data($data = []);
    public function json($data, $status = 200);
}

interface InterfaceCacheFile {
    public function set($name, $data = []);
    public function get($name);
    public function clear($name);
}

interface InterfaceConfig {
    public function set($name, $value);
    public function get($name);
}