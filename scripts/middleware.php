<?php
if (strpos($name_middleware, 'Middleware') === false) $name_middleware = $name_middleware . 'Middleware';
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
    echo "$name_middleware create successfully".PHP_EOL;
} else {
    echo "$name_middleware already exist".PHP_EOL;
}
