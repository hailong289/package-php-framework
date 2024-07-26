<?php
namespace Hola\Interfaces\FormRequestInterface;

interface DataInterface {
    public function value();
    public function toArray();
    public function toJson();
}