<?php
namespace Core\Model;
use \Core\Database;

abstract class Model extends Database
{
    private $db;
    public static $DB;

    public function __construct()
    {
        $this->db = new Database();
        self::$DB = $this->db;
//        $this->db->timestamp = $this->times();
        $this->db->table = $this->table();
        $this->{$this->table()} = $this;

//        var_dump($this->categories);
    }

    abstract public static function table();
    abstract public static function fieldTable();


    public static function get()
    {
        $table = self::table();
        $fieldTable = self::fieldTable();
        if (empty($fieldTable)) {
            $fieldTable = '*';
        }
        $sql = "SELECT {$fieldTable} FROM {$table}";
        $query = self::$DB->query($sql);
        if (!empty($query)) {
            return $query->fetchAll(\PDO::FETCH_ASSOC);
        }
        return false;
    }

    public function first()
    {
        $table = $this->table();
        $fieldTable = $this->fieldTable();
        if (empty($fieldTable)) {
            $fieldTable = '*';
        }
        $sql = "SELECT {$fieldTable} FROM {$table}";
        $query = $this->db->query($sql);
        if (!empty($query)) {
            return $query->fetch(\PDO::FETCH_ASSOC);
        }
        return false;
    }


}