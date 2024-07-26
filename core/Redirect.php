<?php
namespace Hola\Core;
class Redirect {
    public static function to($path = '', $statusCode = 303) {
        header('Location: ' . (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $path, true, $statusCode);
        die();
    }
}