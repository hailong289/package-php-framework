<?php

namespace Hola\Scripts\Commands;

class CreateJobs extends \Hola\Core\Command {
    protected $command = 'create:jobs';
    protected $command_description = 'Create a new job';
    protected $arguments = ['arg_job'];
    protected $options = [];

    public function handle()
    {
        $className = $this->getClassName();
        $concurrentDirectory = __DIR__ROOT . "/App/QueueJobs/$className.php";
        if (file_exists($concurrentDirectory)) {
            $this->output()->text("Jobs $className already exist");
            return false;
        }
        $this->createFolderIfNotExits();
        file_put_contents($concurrentDirectory, $this->rawClass($className), FILE_APPEND);
        if (!file_exists($concurrentDirectory)) {
            $this->output()->text(sprintf('Directory "%s" was not created', $concurrentDirectory));
            return;
        }
        $this->output()->text("Jobs $className create successfully");
    }

    public function getClassName()
    {
        $name = $this->getArgument('arg_job');
        if (strpos($name, 'Job') === false) {
            return $name . 'Job';
        }
        return $name;
    }

    public function createFolderIfNotExits()
    {
        if (!is_dir(__DIR__ROOT . "/App/QueueJobs")) {
            mkdir(__DIR__ROOT . "/App/QueueJobs");
        }
    }

    public function rawClass($className)
    {
        return '<?php
namespace App\QueueJobs;
class '.$className. ' {
   public function __construct(){}
   public function handle(){
       // code here
   }
}';
    }
}