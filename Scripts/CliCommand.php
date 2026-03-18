<?php
namespace Hola\Scripts;
use Symfony\Component\Console\Application;
class CliCommand {
    private Application|null $application = null;

    public function initAppCommand() {
        if (is_null($this->application)) {
            $this->application = new Application();
        }
        return $this;
    }

    public function getAppCommand()
    {
        return $this->application;
    }

    public function register() {
        $array_command = [];
        $command_default = scandir(__DIR__ .'/Commands');
        $command_default = array_diff($command_default, array('.', '..','QueueJobs', 'Schedule'));
        $array_command_default = [];
        if (!empty($command_default)) {
            foreach($command_default as $item){
                $item = str_replace('.php','',$item);
                $array_command[] = app()->make("Hola\\Scripts\\Commands\\$item");
            }
        }

        $command_dir = scandir(__DIR__ROOT .'/App/Commands');
        $command_dir = array_diff($command_dir, array('.', '..', 'Kernel.php'));
        if (!empty($command_dir)) {
            foreach($command_dir as $item){
                $item = str_replace('.php','',$item);
                $array_command[] = app()->make("App\\Commands\\$item");
            }
        }

        if (!empty($array_command)) {
            foreach($array_command as $item){
                if (method_exists($item, 'handle')) {
                    $this->application->add($item);
                }
            }
        }

        return $this;
    }

    public function run() {
        if ($this->application) {
            $this->application->run();
        } else {
            throw new \Exception("Application command not initialized");
        }
    }

}