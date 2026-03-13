<?php
namespace Hola\Core\Hash\GenericHasher;
use Hola\Core\Hash\Interfaces\HashInterface;

class GenericHasher implements HashInterface
{
    private string $algorithm;

    public function __construct(string $algorithm)
    {
        if (!in_array($algorithm, hash_algos())) {
            throw new Exception("Algorithm not supported");
        }

        $this->algorithm = $algorithm;
    }

    public function make(string $data): string
    {
        return hash($this->algorithm, $data);
    }

    public function verify(string $data, string $hash): bool
    {
        return hash($this->algorithm, $data) === $hash;
    }
}