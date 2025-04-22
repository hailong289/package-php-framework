<?php

namespace Hola\Scripts\Commands;

class CreateCommand extends \Hola\Core\Command {
    protected $command = 'create:command';
    protected $command_description = 'Create a new command';
    protected $arguments = ['arg_command'];
    protected $options = [];


    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $className = $this->getClassName();
        $concurrentDirectory = __DIR__ROOT . "/App/Commands/$className.php";
        if (file_exists($concurrentDirectory)) {
            $this->output()->text("$className already exist".PHP_EOL);
            return;
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
        $name = $this->getArgument('arg_command');
        if (strpos($name, 'Command') === false) {
            return $name . 'Command';
        }
        return $name;
    }

    public function createFolderIfNotExits()
    {
        if (!is_dir(__DIR__ROOT . "/App/Commands")) {
            mkdir(__DIR__ROOT . "/App/Commands");
        }
    }

    public function rawClass($className)
    {
        return '<?php
namespace App\Commands;
use Hola\Core\Command;
class '.$className.' extends Command {
    public function __construct()
    {
        parent::__construct();
    }
    protected $command = "command_name";
    protected $command_description = "A command description";
    protected $arguments = [];
    protected $options = [];

    public function handle()
    {
        // code here
    }
}';
    }
}