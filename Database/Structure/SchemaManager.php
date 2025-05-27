<?php

namespace Hola\Database\Structure;
use Hola\Database\QueryConnectBuilder;

class SchemaManager {
    private static QueryConnectBuilder|null $connection = null;
    
    public function __construct()
    {
        $this->connect();
    }

    /**
     * Get Connection instance
     * @return QueryConnectBuilder
     */
    public static function connect() {
        if (is_null(self::$connection)) {
            self::$connection = new QueryConnectBuilder();
        }
        return self::$connection;
    }

    /**
     * excute sql
     * @param string $sql
     * @return bool
     */
    public function execute($sql)
    {
        return self::$connection->query($sql);
    }

    /**
     * create table
     * @param string $tableName
     * @param callable $callback
     * @return bool
     */
    public function createTable($tableName, $callback)
    {
        $table = new Table();
        $callback($table);
        $sql_column = $table->toSql();
        $sql = "CREATE TABLE $tableName (";
        $sql .= $sql_column;
        $sql .= ");";
        return $this->execute($sql);
    }

    /**
     * create table if not exists
     * @param string $tableName
     * @param callable $callback
     * @return bool
     */
    public function createTableIfNotExists($tableName, $callback)
    {
        $table = new Table();
        $callback($table);
        $sql_column = $table->toSql();
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (";
        $sql .= $sql_column;
        $sql .= ");";
        return $this->execute($sql);
    }

    /**
     * use table
     * @param string $tableName
     * @param callable $callback
     * @return bool
     */
    public function useTable($tableName, $callback)
    {
        $table = new Table(true);
        $callback($table);
        $sql_column = $table->toSql();
        $sql = "ALTER TABLE $tableName ";
        $sql .= $sql_column;
        $sql .= ";";
        return $this->execute($sql);
    }

}