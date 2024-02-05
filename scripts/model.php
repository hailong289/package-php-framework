<?php
if(count($argv)) unset($argv[0]);
$argv = array_values($argv);
if(!empty($argv[0])) {
    $command = explode(':',$argv[0]);
    if(empty($command[1])) {
        echo "Name Model not null";
    }
    $command_table = explode('=',$argv[1]);
    $table = $command_table[1] ?? 'default';
    if($command[0] === 'create') {
        if (!file_exists(dirname(__DIR__, 1) .'/app/Models')) {
            if (!mkdir($concurrentDirectory = dirname(__DIR__, 1) . '/app/Models', 0777, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }
        if(!file_exists(dirname(__DIR__, 1) ."/app/Models/$command[1].php")) {
            file_put_contents(dirname(__DIR__, 1) ."/app/Models/$command[1].php",'<?php
namespace App\Models;
use App\Core\Model;

class '.$command[1].' extends Model {
    protected static $tableName = "'.$table.'";
    protected static $times_auto = false;
    protected static $date_create = "date_created";
    protected static $date_update = "date_update";
    protected static $field = [];
}', FILE_APPEND);
        } else {
            echo "Model $command[1] already exist";
        }
    }
}