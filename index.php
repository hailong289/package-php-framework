<?php
session_start();
use App\App;
try {
    require_once "bootstrap.php";
    $app = new App;
}catch (Throwable $e) {
    $code = (int)$e->getCode();
    http_response_code($code ? $code:500);
    throw $e;
}
