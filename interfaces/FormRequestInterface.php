<?php
namespace System\Interfaces\FormRequestInterface;

interface DataInterface {
    public function value();
    public function toArray();
    public function toJson();
}