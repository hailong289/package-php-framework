<?php

namespace Hola\Database\Structure;

class DBSchema {
    private static SchemaManager|null $schemaMG = null;

    public static function schemaManage(): SchemaManager
    {
        if (is_null(self::$schemaMG)) {
            self::$schemaMG = new SchemaManager();
        }
        return self::$schemaMG;
    }

    public static function createTable($tableName, $callback)
    {
        $schema = self::schemaManage();
        if (is_null($callback)) {
            $sql = "CREATE TABLE $tableName;";
            return $schema->execute($sql);
        }
        return $schema->createTable($tableName, $callback);
    }

    public static function useTable($tableName, $callback)
    {
        $schema = self::schemaManage();
        return $schema->useTable($tableName, $callback);
    }

    
    public static function dropTable($tableName)
    {
        $schema = self::schemaManage();
        $sql = "DROP TABLE IF EXISTS $tableName;";
        return $schema->execute($sql);
    }
    
    public static function hasTable($tableName)
    {
        $schema = self::schemaManage();
        $sql = "SHOW TABLES LIKE '$tableName';";
        $execute = $schema->execute($sql);
        return $execute->rowCount() > 0;
    }
    
    public static function dropColumn($tableName, $columnName)
    {
        $schema = self::schemaManage();
        $sql = "ALTER TABLE $tableName DROP COLUMN $columnName;";
        return $schema->execute($sql);
    }
}