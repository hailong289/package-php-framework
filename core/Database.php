<?php
namespace App\Core;
use Core\Builder\QueryBuilder;

class Database {
    private static $__conn;
    private static $enableLog = false;
    private static $log = [];
    private static $class;
    use QueryBuilder;
    public function __construct()
    {
        self::$__conn = Connection::getInstance(DB_ENVIRONMENT, DB_CONNECTION);
        self::$class = $this;
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
        try {
            if(empty(self::$log)){
                throw new \RuntimeException("No sql result",500);
            }
            echo "<pre>";
            print_r(self::$log);
            echo "</pre>";
            exit();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public static function beginTransaction(){
        return self::$__conn->beginTransaction();
    }

    public static function commit(){
        return self::$__conn->commit();
    }

    public static function rollBack(){
        return self::$__conn->rollBack();
    }

    public static function modelInstance() {
        return new static();
    }
}

