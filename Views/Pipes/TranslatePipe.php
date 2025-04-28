<?php
namespace Hola\Views\Pipes;
use Hola\Views\Interfaces\IPipes;
class TranslatePipe implements IPipes {
    public function handle($value)
    {
        return translate($value);
    }
}