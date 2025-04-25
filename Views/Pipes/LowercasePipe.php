<?php
namespace Hola\Views\Pipes;
use Hola\Views\Interfaces\IPipes;

class LowercasePipe implements IPipes {
    public function handle($value)
    {
        return strtolower($value);
    }
}