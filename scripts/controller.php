<?php
if(count($argv)) unset($argv[0]);
$argv = array_values($argv);
if(!empty($argv[0])) {
    $command = explode(':',$argv[0]);
    if($argv[0] === 'create:controller') {
        $name_controller = $argv[1];
        if(empty($name_controller)) {
            echo "Name Controller not null";
        }
        if(strpos($name_controller, 'Controller') === false) $name_controller = $name_controller.'Controller';
        $folder = explode('/', $name_controller);
        if (count($folder) > 1) {
            unset($folder[count($folder) - 1]);
            $folder = '/' . implode('/', $folder);
        } else {
            $folder = $name_controller;
        }
        $concurrentDirectory = __DIR__ROOT . "/App/Controllers/$folder.php";
        if (!file_exists($concurrentDirectory)) {
            file_put_contents($concurrentDirectory, "<?php
namespace App\Controllers;
use System\Core\BaseController;
class {$name_controller} extends BaseController {
    public function __construct(){}
} 
", FILE_APPEND);
            if (!file_exists($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        } else {
            echo "Controller $name_controller already exist";
        }
    }
}