<?php
namespace App\Core;
use Core\Builder\QueryBuilder;
use App\Core\Database;

abstract class Model extends Database
{
    use QueryBuilder;
    private static $DB;
    private static $query = '';
    private static $class;

    public function __construct()
    {
        self::$class = $this;
        self::$DB = new Database();
    }

    public static function custom($sql = ''){
        self::$query = self::modelInstance()->query($sql);
        return self::modelInstance();
    }

    public static function modelInstance() {
        return new static();
    }

}