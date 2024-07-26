<?php
namespace Scripts;
class ModelScript extends \Hola\Core\Command
{
    protected $command = 'create:model';
    protected $command_description = 'Create a new model';
    protected $arguments = [
        'name_model'
    ];
    protected $options = ['table'];


    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $name_model = $this->getArgument('name_model');
        $name_table = $this->getOption('table') ?? 'default';
        $concurrentDirectory = __DIR__ROOT . "/App/Models/$name_model.php";
        if (!file_exists($concurrentDirectory)) {
            if (!is_dir(__DIR__ROOT . "/App/Models")) {
                mkdir(__DIR__ROOT . "/App/Models");
            }
            file_put_contents($concurrentDirectory, '<?php
namespace App\Models;
use Hola\Core\Model;

class ' . $name_model . ' extends Model {
    protected static $tableName = "' . $name_table . '";
    protected static $times_auto = false;
    protected static $date_create = "date_created";
    protected static $date_update = "date_updated";
    protected static $field = [];
}', FILE_APPEND);
            if (!file_exists($concurrentDirectory)) {
               $this->output()->text(sprintf('Directory "%s" was not created', $concurrentDirectory));
               return;
            }
            $this->output()->text("Model $name_model create successfully");
        } else {
            $this->output()->text("Model $name_model already exist");
        }
    }
}

