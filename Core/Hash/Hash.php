<?php
namespace Hola\Core;
use Hola\Core\Hash\Argon2Hasher;
use Hola\Core\Hash\BcryptHasher;
use Hola\Core\Hash\GenericHasher;
use Hola\Core\Hash\Interfaces\HashInterface;

class Hash
{
    public static function create(string $type): HashInterface
    {
        switch ($type) {

            case "bcrypt":
                return new BcryptHasher();

            case "argon2":
                return new Argon2Hasher();

            case "sha256":
            case "sha512":
            case "md5":
            case "sha1":
            case "ripemd160":
                return new GenericHasher($type);

            default:
                throw new Exception("Unsupported hash algorithm");
        }
    }

    public static function argon2() {
        return new Argon2Hasher();
    }

    public static function bcrypt() {
        return new BcryptHasher();
    }

    public static function sha256() {
        return new GenericHasher("sha256");
    }

    public static function sha512() {
        return new GenericHasher("sha512");
    }

    public static function md5() {
        return new GenericHasher("md5");
    }

    public static function sha1() {
        return new GenericHasher("sha1");
    }

    public static function ripemd160() {
        return new GenericHasher("ripemd160");
    }

}