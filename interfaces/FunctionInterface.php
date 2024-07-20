<?php

namespace System\Interfaces\FunctionInterface;

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

interface InterfaceSendJob {
    public function connection($name);
    public function timeout($timeout);
    public function queue($name);
    public function work();
}

interface InterfaceCacheFile {
    public function set($name, $data = []);
    public function get($name);
    public function clear($name);
}