<?php

namespace Hola\Database\Structure;
use Hola\Database\Connection;

class SchemaManager {
    private static Connection|null $connection = null;
    
    public function __construct()
    {
        $this->connect();
    }
   
    public static function connect() {
        if (is_null(self::$connection)) {
            self::$connection = new Connection();
        }
        return self::$connection;
    }
    
    public function execute($sql)
    {
        return self::$connection->query($sql); 
    }

    public function createTable($tableName, $callback)
    {
        $table = new Table();
        $callback($table);
        $sql_column = $table->toSql();
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (";
        $sql .= $sql_column;
        $sql .= ");";
        return $this->execute($sql);
    }
    
    public function useTable($tableName, $callback)
    {
        $params = new Table(true);
        $callback($table);
        $sql_column = $params->toSql();
        $sql = "ALTER TABLE $tableName ";
        $sql .= $sql_column;
        $sql .= ";";
        return $this->execute($sql);
    }
    
}