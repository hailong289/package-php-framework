<?php
namespace System\Core;
use System\Trait\QueryBuilder;

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

    public function getCollection($data) {
        $collection = new Collection($data);
        return $collection;
    }
}

