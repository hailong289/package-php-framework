<?php
if(count($argv)) unset($argv[0]);
$argv = array_values($argv);
if(!empty($argv[0])) {
    $command = explode(':',$argv[0]);
    if(empty($command[1])) {
        echo "Name Controller not null";
    }
    if(strpos($command[1], 'Controller') === false) $command[1] = $command[1].'Controller';
    if($command[0] === 'create') {
        $folder = explode('/',$command[1]);
        if(count($folder) > 1){
            unset($folder[count($folder)-1]);
            $folder = '/'.implode('/',$folder);
        } else $folder = $command[1];
        if (!file_exists(dirname(__DIR__, 1) .'/app/Controllers'.$folder)) {
            if (!mkdir($concurrentDirectory = dirname(__DIR__, 1) . '/app/Controllers'.$folder, 0777, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }
        if(!file_exists(dirname(__DIR__, 1) ."/app/Controllers/$command[1].php")) {
            file_put_contents(dirname(__DIR__, 1) ."/app/Controllers/$command[1].php","<?php
namespace App\Controllers;
use System\Core\BaseController;
class {$command[1]} extends BaseController {
    public function __construct(){}
} 
", FILE_APPEND);
        } else {
            echo "Controller $command[1] already exist";
        }
    }
}