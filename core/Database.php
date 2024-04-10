<?php
namespace System\Core;
use System\Traits\QueryBuilder;

class Database {
    private static $__conn;
    private static $enableLog = false;
    private static $log = [];
    private static $collection;
    use QueryBuilder;
    public function __construct()
    {
        self::$__conn = Connection::getInstance(DB_ENVIRONMENT, DB_CONNECTION);
    }

    public function query($sql, $last_id = false){
        try {
            $statement = self::$__conn->prepare($sql);
            $statement->execute();
            if(self::$enableLog) self::$log[] = $statement;
            if($last_id) return self::$__conn->lastInsertId();
            return $statement;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public static function enableQueryLog(){
        self::$enableLog = true;
    }

    public static function getQueryLog(){
        if(!self::$enableLog){
            throw new \RuntimeException("Not enable sql log",500);
        }
        return self::$log ?? '';
    }

    public static function connection($env, $connection = 'mysql'){
        self::$__conn = Connection::getInstance($env, $connection);
        return new static();
    }

    public static function beginTransaction(){
        if(self::$__conn == null) {
            self::$__conn = Connection::getInstance(DB_ENVIRONMENT, DB_CONNECTION);
        }
        return self::$__conn->beginTransaction();
    }

    public static function commit(){
        if(self::$__conn == null) {
            self::$__conn = Connection::getInstance(DB_ENVIRONMENT, DB_CONNECTION);
        }
        return self::$__conn->commit();
    }

    public static function rollBack(){
        if(self::$__conn == null) {
            self::$__conn = Connection::getInstance(DB_ENVIRONMENT, DB_CONNECTION);
        }
        return self::$__conn->rollBack();
    }

    public static function modelInstance() {
        return new static();
    }

    private function getCollection($data) {
        $collection = new Collection($data);
        return $collection;
    }

    public static function get(){
        $sql = self::sqlQuery();
        $instance = static::modelInstance();
        $data = $instance->query($sql)
            ->fetchAll(\PDO::FETCH_OBJ);
        if (!empty($data)) {
            return $instance->getCollection($data)->map(fn ($item) => self::getAttribute($item));
        }
        return $instance->getCollection([]);
    }

    public static function getArray(){
        $sql = self::sqlQuery();
        $instance = static::modelInstance();
        $data = $instance->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        if (!empty($data)) {
            return array_map(function ($item){
                return self::getAttribute($item, true);
            }, $data ?? []);
        }
        return false;
    }

    public static function first(){
        $sql = self::sqlQuery();
        $instance = static::modelInstance();
        $data = $instance->query($sql)->fetch(\PDO::FETCH_OBJ);
        if (!empty($data)) {
            return $instance->getCollection($data)->mapFirst(fn ($item) => self::getAttribute($item));
        }
        return false;
    }

    public static function firstArray(){
        $sql = self::sqlQuery();
        $instance = static::modelInstance();
        $data = $instance->query($sql)->fetch(\PDO::FETCH_ASSOC);
        if (!empty($data)) {
            return self::getAttribute($data, true);
        }
        return false;
    }

    public static function findById($id) {
        $tableName = self::$tableName ? self::$tableName:static::$tableName;
        $instance = static::modelInstance();
        $sql = "SELECT * FROM {$tableName} WHERE id = '$id'";
        $data = $instance->query($sql)->fetch(\PDO::FETCH_OBJ);
        if (!empty($data)) {
            return $instance->getCollection($data)->mapFirst(fn ($item) => self::getAttribute($item));
        }
        return false;
    }

    public static function find($id) {
        $tableName = self::$tableName ? self::$tableName:static::$tableName;
        $instance = static::modelInstance();
        $sql = "SELECT * FROM {$tableName} WHERE id = '$id'";
        $data = $instance->query($sql)->fetch(\PDO::FETCH_OBJ);
        if (!empty($data)) {
            return $instance->getCollection($data)->mapFirst(fn ($item) => self::getAttribute($item));
        }
        return false;
    }

}

