<?php
namespace Hola\Core;

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

class Cookie {
    public static function set($key, $value, $time = 3600){ // Mặc định lưu trong 1 giờ
        setcookie($key, $value, time() + $time, "/");
        return new static();
    }

    public static function get($key){
        return $_COOKIE[$key] ?? null;
    }

    public static function remove($key){
        unset($_COOKIE[$key]);
        setcookie($key, null, -1, '/');
    }

    public static function isExited($key){
        if(!isset($_COOKIE[$key])) {
            return false;
        } else {
            return true;
        }
    }
}