<?php
namespace Hola\Scripts\Commands;
class CreateController extends \Hola\Core\Command
{
    protected $command = 'create:controller';
    protected $command_description = 'Create a new controller';
    protected $arguments = ['arg_controller'];
    protected $options = [];


    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $classController = $this->getClassName();
        $concurrentDirectory = __DIR__ROOT . "/App/Http/Controllers/$classController.php";
        if (file_exists(__DIR__ROOT . "/App/Http/Controllers/$classController.php")) {
            $this->output()->text("$classController already exist");
            return false;
        }
        file_put_contents(__DIR__ROOT . "/App/Http/Controllers/$classController.php", $this->rawClass($classController), FILE_APPEND);
        if (!file_exists(__DIR__ROOT . "/App/Http/Controllers/$classController.php")) {
            $this->output()->text(sprintf('Directory "%s" was not created', $concurrentDirectory));
            return;
        }
        $this->output()->text("$classController create successfully");
    }

    public function getClassName()
    {
        $name = $this->getArgument('arg_controller');
        if (strpos($name, 'Controller') === false) {
            return $name . 'Controller';
        }
        return $name;
    }

    public function getFullDirController($classController)
    {
        $folder = explode('/', $classController);
        if (count($folder) > 1) {
            unset($folder[count($folder) - 1]);
            $folder = implode('/', $folder);
            if (!file_exists(__DIR__ROOT .'/App/Http/Controllers/'.$folder)) {
                if (!mkdir($concurrentDirectory = __DIR__ROOT . '/App/Http/Controllers/'.$folder, 0777, true) && !is_dir($concurrentDirectory)) {
                    $this->output()->text(sprintf('Directory "%s" was not created', $concurrentDirectory));
                    return;
                }
            }
        } else {
            $folder = $classController;
        }
        return $folder;
    }

    public function createFolderIfNotExits()
    {
        if (!is_dir(__DIR__ROOT . "/App/Http/Controllers")) {
            mkdir(__DIR__ROOT . "/App/Http/Controllers");
        }
    }

    public function rawClass($className)
    {
        return "<?php
namespace App\Http\Controllers;
class {$name_controller} {
    public function __construct(){}
} 
";
    }




}
