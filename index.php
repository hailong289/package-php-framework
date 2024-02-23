<?php
session_start();
use System\App;
try {
    require 'vendor/autoload.php';
    require_once "bootstrap.php";
    $app = new App();
    $app->run();
}catch (Throwable $e) {
    $code = (int)$e->getCode();
    $date = "[".date('Y-m-d H:i:s')."]: ";
    if (!file_exists(__DIR__ROOT .'/storage')) {
        if (!mkdir($concurrentDirectory = __DIR__ROOT . '/storage', 0777, true) && !is_dir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }
    }
    file_put_contents(__DIR__ROOT .'/storage/debug.log',$date . $e . PHP_EOL.PHP_EOL, FILE_APPEND);
    http_response_code($code ? $code:500);
    echo $e;
    throw $e;
}
