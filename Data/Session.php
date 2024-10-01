<?php
namespace Hola\Data;
class Session {
    public static function set($key, $value){
        $_SESSION[$key] = $value;
        return new static();
    }

    public static function get($key){
        return $_SESSION[$key] ?? null;
    }

    public static function remove($key){
        unset($_SESSION[$key]);
    }

    public static function isExited($key){
        if(!isset($_SESSION[$key])) {
            return false;
        } else {
            return true;
        }
    }
}