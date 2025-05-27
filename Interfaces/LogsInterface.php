<?php
namespace Hola\Interfaces\InterfaceLogs;

interface Log {
    public function dump(...$args);
    public function dump_html(...$args);
    public function write(array $data, $name_file = 'application');
    public function debug(array $data);
    public function write_error(\Throwable $e, $name_file = 'application');
}