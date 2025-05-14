<?php

namespace Hola\Database\Structure;

class DBSchema {
    private static SchemaManager|null $schemaMG = null;

    /**
     * Get SchemaManager instance
     * @return SchemaManager
     */
    public static function schemaManage(): SchemaManager
    {
        if (is_null(self::$schemaMG)) {
            self::$schemaMG = new SchemaManager();
        }
        return self::$schemaMG;
    }

    /**
     * Create table
     * @param string $tableName
     * @param callable|null $callback
     * @return bool
     */
    public static function createTable($tableName, $callback)
    {
        $schema = self::schemaManage();
        if (is_null($callback)) {
            $sql = "CREATE TABLE $tableName;";
            return $schema->execute($sql);
        }
        return $schema->createTable($tableName, $callback);
    }

    /**
     * Create table if not exists
     * @param string $tableName
     * @param callable|null $callback
     * @return bool
     */
    public static function createTableIfNotExists($tableName, $callback)
    {
        $schema = self::schemaManage();
        if (is_null($callback)) {
            $sql = "CREATE TABLE IF NOT EXISTS $tableName;";
            return $schema->execute($sql);
        }
        return $schema->createTableIfNotExists($tableName, $callback);
    }

    /**
     * use table
     * @param string $tableName
     * @param callable|null $callback
     * @return bool
     */
    public static function useTable($tableName, $callback)
    {
        $schema = self::schemaManage();
        return $schema->useTable($tableName, $callback);
    }

    /**
     * drop table if not exists
     * @param string $tableName
     * @return bool
     */
    public static function dropTable($tableName)
    {
        $schema = self::schemaManage();
        $sql = "DROP TABLE IF EXISTS $tableName;";
        return $schema->execute($sql);
    }

    /**
     * Check if table exists
     * @param string $tableName
     * @return bool
     */
    public static function hasTable($tableName)
    {
        $schema = self::schemaManage();
        $sql = "SHOW TABLES LIKE '$tableName';";
        $execute = $schema->execute($sql);
        return $execute->rowCount() > 0;
    }

    /**
     * drop column
     * @param string $tableName
     * @param string $columnName
     * @return bool
     */
    public static function dropColumn($tableName, $columnName)
    {
        $schema = self::schemaManage();
        $sql = "ALTER TABLE $tableName DROP COLUMN $columnName;";
        return $schema->execute($sql);
    }
}