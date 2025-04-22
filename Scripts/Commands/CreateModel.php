<?php
namespace Hola\Scripts\Commands;
class CreateModel extends \Hola\Core\Command
{
    protected $command = 'create:model';
    protected $command_description = 'Create a new model';
    protected $arguments = ['arg_model'];
    protected $options = ['table'];


    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $className = $this->getClassName();
        $concurrentDirectory = __DIR__ROOT . "/App/Models/$className.php";
        if (file_exists($concurrentDirectory)) {
            $this->output()->text("Model $name_model already exist");
            return false;
        }
        $this->createFolderIfNotExits();
        file_put_contents($concurrentDirectory, $this->rawClass($className), FILE_APPEND);
        if (!file_exists($concurrentDirectory)) {
            $this->output()->text(sprintf('Directory "%s" was not created', $concurrentDirectory));
            return;
        }
        $this->output()->text("Model $name_model create successfully");
    }

    public function getClassName()
    {
        $name = $this->getArgument('arg_model');
        if (strpos($name, 'Model') === false) {
            return $name . 'Model';
        }
        return $name;
    }

    public function createFolderIfNotExits()
    {
        if (!is_dir(__DIR__ROOT . "/App/Models")) {
            mkdir(__DIR__ROOT . "/App/Models");
        }
    }

    public function rawClass($className)
    {
        $name_table = $this->getOption('table') ?? 'default';
        return '<?php
namespace App\Models;
use Hola\Database\Model;

class ' . $className . ' extends Model {
    protected static $tableName = "' . $name_table . '";
    protected static $times_auto = false;
    protected static $date_create = "date_created";
    protected static $date_update = "date_updated";
    protected static $field = [];
}';
    }
}

