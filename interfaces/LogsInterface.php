<?php
namespace System\Interfaces\InterfaceLogs;

interface Log {
    public function dump(...$args);
    public function write($data, $name_file = 'debug');
    public function debug($data);
}