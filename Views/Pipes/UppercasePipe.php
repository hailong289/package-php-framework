<?php
namespace Hola\Views\Pipes;
use Hola\Views\Interfaces\IPipes;

class UppercasePipe implements IPipes {
    public function handle($value)
    {
        return strtoupper($value);
    }
}