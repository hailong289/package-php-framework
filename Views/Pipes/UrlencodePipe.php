<?php

namespace Hola\Views\Pipes;
use Hola\Views\Interfaces\IPipes;

class UrlencodePipe implements IPipes {
    public function handle($value)
    {
        return urlencode($value);
    }
}