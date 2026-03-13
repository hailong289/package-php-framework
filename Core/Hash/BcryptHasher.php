<?php
namespace Hola\Core\Hash;
use Hola\Core\Hash\Interfaces\HashInterface;

class BcryptHasher implements HashInterface
{
    public function make(string $data): string
    {
        return password_hash($data, PASSWORD_BCRYPT);
    }

    public function verify(string $data, string $hash): bool
    {
        return password_verify($data, $hash);
    }
}