<?php
namespace Core\Model;
use Core\Builder\QueryBuilder;
use \Core\Database;

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
        self::$query = self::$DB->query($sql);
        return self::$class;
    }

}