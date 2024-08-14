<?php
namespace Hola\Scripts;
use Hola\Core\Command;

class CommandScript extends \Hola\Core\Command
{
    protected $command = 'create:command';
    protected $command_description = 'Create a new command';
    protected $arguments = [
        'name_command'
    ];
    protected $options = [];


    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $name_command = $this->getArgument('name_command');
        if (strpos($name_command, 'Command') === false) $name_command = $name_command . 'Command';
        $concurrentDirectory = __DIR__ROOT . "/commands/$name_command.php";
        if (!file_exists($concurrentDirectory)) {
            if (!is_dir(__DIR__ROOT . "/commands")) {
                mkdir(__DIR__ROOT . "/commands");
            }
            file_put_contents($concurrentDirectory, '<?php
namespace Commands;
use Hola\Core\Command;
class '.$name_command.' extends Command {
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
}', FILE_APPEND);
            if (!file_exists($concurrentDirectory)) {
                $this->output()->text(sprintf('Directory "%s" was not created', $concurrentDirectory));
                return;
            }
            $this->output()->text("$name_command create successfully".PHP_EOL);
        } else {
            $this->output()->text("$name_command already exist".PHP_EOL);
        }
    }
}
