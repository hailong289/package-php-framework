<?php
if(count($argv)) unset($argv[0]);
$argv = array_values($argv);
if(!empty($argv[0])) {
    $command = explode(':',$argv[0]);
    if(empty($command[1])) {
        echo "Name middleware not null";
    }
    if(strpos($command[1], 'Middleware') === false) $command[1] = $command[1].'Middleware';
    if($command[0] === 'create') {
        if (!file_exists(dirname(__DIR__, 1) .'/middleware')) {
            if (!mkdir($concurrentDirectory = dirname(__DIR__, 1) . '/middleware', 0777, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }
        if(!file_exists(dirname(__DIR__, 1) ."/middleware/$command[1].php")) {
            file_put_contents(dirname(__DIR__, 1) ."/middleware/$command[1].php",'<?php
namespace System\Middleware;
use System\Core\Request;

class '.$command[1].' {
     public function handle(Request $request){
         return $request->next();
     }
}
', FILE_APPEND);
        } else {
            echo "Middleware $command[1] already exist";
        }
    }
}