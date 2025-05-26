<?php
namespace Hola\Interfaces\InterfaceLogs;

interface Log {
    public function dump(...$args);
    public function dump_html(...$args);
    public function write($data, $name_file = 'debug');
    public function debug($data);
    public function write_error($data, $name_file = 'debug');
}