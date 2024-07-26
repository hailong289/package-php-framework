<?php
namespace Scripts;
class ControllerScript extends \Hola\Core\Command
{
    protected $command = 'create:controller';
    protected $command_description = 'Create a new controller';
    protected $arguments = [
        'name_controller'
    ];
    protected $options = [];


    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $name_controller = $this->getArgument('name_controller');
        if (strpos($name_controller, 'Controller') === false) $name_controller = $name_controller . 'Controller';
        $folder = explode('/', $name_controller);
        if (count($folder) > 1) {
            unset($folder[count($folder) - 1]);
            $folder = implode('/', $folder);
            if (!file_exists(__DIR__ROOT .'/App/Controllers/'.$folder)) {
                if (!mkdir($concurrentDirectory = __DIR__ROOT . '/App/Controllers/'.$folder, 0777, true) && !is_dir($concurrentDirectory)) {
                    $this->output()->text(sprintf('Directory "%s" was not created', $concurrentDirectory));
                    return;
                }
            }
        } else {
            $folder = $name_controller;
        }
        $concurrentDirectory = __DIR__ROOT . "/App/Controllers/$name_controller.php";
        if (!file_exists(__DIR__ROOT . "/App/Controllers/$name_controller.php")) {
            file_put_contents(__DIR__ROOT . "/App/Controllers/$name_controller.php", "<?php
namespace App\Controllers;
use Hola\Core\BaseController;
class {$name_controller} extends BaseController {
    public function __construct(){}
} 
", FILE_APPEND);
            if (!file_exists(__DIR__ROOT . "/App/Controllers/$name_controller.php")) {
                $this->output()->text(sprintf('Directory "%s" was not created', $concurrentDirectory));
                return;
            }
            $this->output()->text("$name_controller create successfully");
        } else {
            $this->output()->text("$name_controller already exist");
        }
    }
}
