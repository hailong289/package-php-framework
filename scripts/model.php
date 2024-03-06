<?php
$concurrentDirectory = __DIR__ROOT . "/App/Models/$name_model.php";
if (!file_exists($concurrentDirectory)) {
    file_put_contents($concurrentDirectory, '<?php
namespace App\Models;
use System\Core\Model;

class ' . $name_model . ' extends Model {
    protected static $tableName = "' . $name_table . '";
    protected static $times_auto = false;
    protected static $date_create = "date_created";
    protected static $date_update = "date_update";
    protected static $field = [];
}', FILE_APPEND);
    if (!file_exists($concurrentDirectory)) {
        throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
    }
    echo "Model $name_model create successfully";
} else {
    echo "Model $name_model already exist";
}
