<?php

namespace Hola\Scripts\Commands;

use Hola\Core\Command;

class CreateMiddleware extends Command {
    protected $command = 'create:middleware';
    protected $command_description = 'Create a new middleware';
    protected $arguments = ['arg_middleware'];
    protected $options = [];


    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $className = $this->getClassName();
        $concurrentDirectory = __DIR__ROOT . "/App/Http/Middleware/$className.php";
        if (file_exists($concurrentDirectory)) {
            $this->output()->text("$className already exist".PHP_EOL);
            return false;
        }
        $this->createFolderIfNotExits();
        file_put_contents($concurrentDirectory, $this->rawClass($className), FILE_APPEND);
        if (!file_exists($concurrentDirectory)) {
            $this->output()->text(sprintf('Directory "%s" was not created', $concurrentDirectory));
            return;
        }
        $this->output()->text("$className create successfully".PHP_EOL);
    }

    public function getClassName()
    {
        $name = $this->getArgument('arg_middleware');
        if (strpos($name, 'Middleware') === false) {
            return $name . 'Middleware';
        }
        return $name;
    }

    public function createFolderIfNotExits()
    {
        if (!is_dir(__DIR__ROOT . "/App/Http/Middleware")) {
            mkdir(__DIR__ROOT . "/App/Http/Middleware");
        }
    }


    public function rawClass($className)
    {

        return '<?php
namespace App\Http\Middleware;
use Hola\Transport\Request;
use Hola\Transport\Response;

class ' . $className . ' {
     public function forward(Request $request, \Closure $continue){
         return $continue($request);
     }
}
';
    }

}