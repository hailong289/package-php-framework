<?php
namespace Hola\Core\Hash\Interfaces;
interface HashInterface
{
    public function make(string $data): string;
    public function verify(string $data, string $hash): bool;
}