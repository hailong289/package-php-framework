<?php
namespace Hola\Transport\Interface;
interface IKernel {
     public function getRequiredMiddleWares();
     public function getMiddleWares();
}