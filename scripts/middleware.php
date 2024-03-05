<?php
if(count($argv)) unset($argv[0]);
$argv = array_values($argv);
if(!empty($argv[0])) {
    $command = explode(':',$argv[0]);
    if($argv[0] === 'create:middleware') {
        $name_middleware = $argv[1];
        if(empty($name_middleware)) {
            echo "Name Middleware not null";
        }
        if(strpos($name_middleware, 'Middleware') === false) $name_middleware = $name_middleware.'Middleware';
        $concurrentDirectory = __DIR__ROOT . "/middleware/$name_middleware.php";
        if (!file_exists($concurrentDirectory)) {
            file_put_contents($concurrentDirectory, '<?php
namespace System\Middleware;
use System\Core\Request;

class ' . $name_middleware . ' {
     public function handle(Request $request){
         return $request->next();
     }
}
', FILE_APPEND);
            if (!file_exists($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        } else {
            echo "Middleware $command[1] already exist";
        }
    }
}