<?php
namespace Hola\Scripts\Commands;
use Hola\Core\Command;

class CreatePipe extends Command
{
    protected $command = 'create:pipe';
    protected $command_description = 'Create a pipe';
    protected $arguments = ['arg_pipe'];
    protected $options = [];


    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $classPipe = $this->getClassName();
        $concurrentDirectory = __DIR__ROOT . "/App/Pipes/$classPipe.php";
        if (file_exists($concurrentDirectory)) {
            $this->output()->text("$classPipe already exist");
            return false;
        }
        $this->createFolderIfNotExits();
        file_put_contents($concurrentDirectory, $this->rawClass($classPipe), FILE_APPEND);
        if (!file_exists($concurrentDirectory)) {
            $this->output()->text(sprintf('Directory "%s" was not created', $concurrentDirectory));
            return;
        }
        $this->output()->text("$classPipe create successfully");
    }

    public function createFolderIfNotExits()
    {
        if (!is_dir(__DIR__ROOT . "/App/Pipes")) {
            mkdir(__DIR__ROOT . "/App/Pipes");
        }
    }
    
    public function getClassName()
    {
        $name = $this->getArgument('arg_pipe');
        if (strpos($name, 'Pipe') === false) {
            return $name . 'Pipe';
        }
        return $name;
    }
    
    public function rawClass($classPipe)
    {
        return '<?php
namespace Hola\Views\Pipes;
use Hola\Views\Interfaces\IPipes;
class '.$classPipe.' implements IPipes {
    public function handle($value)
    {
        // TODO: Implement handle() method.
        return $value;
    }
}';
    }
}