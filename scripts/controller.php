<?php
if (strpos($name_controller, 'Controller') === false) $name_controller = $name_controller . 'Controller';
$folder = explode('/', $name_controller);
if (count($folder) > 1) {
    unset($folder[count($folder) - 1]);
    $folder = implode('/', $folder);
    if (!file_exists(__DIR__ROOT .'/App/Controllers/'.$folder)) {
        if (!mkdir($concurrentDirectory = __DIR__ROOT . '/App/Controllers/'.$folder, 0777, true) && !is_dir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            exit();
        }
    }
} else {
    $folder = $name_controller;
}
$concurrentDirectory = __DIR__ROOT . "/App/Controllers/$name_controller.php";
if (!file_exists(__DIR__ROOT . "/App/Controllers/$name_controller.php")) {
    file_put_contents(__DIR__ROOT . "/App/Controllers/$name_controller.php", "<?php
namespace App\Controllers;
use System\Core\BaseController;
class {$name_controller} extends BaseController {
    public function __construct(){}
} 
", FILE_APPEND);
    if (!file_exists(__DIR__ROOT . "/App/Controllers/$name_controller.php")) {
        throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
    }
    echo "$name_controller create successfully";
} else {
    echo "$name_controller already exist";
}
