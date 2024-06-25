<?php
namespace System\Core;
use System\Traits\QueryBuilder;

class Database {
    use QueryBuilder;
    private static $__conn;
    private static $enableLog = false;
    private static $log = [];
    private static $collection;
    private $model;
    public function __construct($model = '', $column = [])
    {
        self::$__conn = Connection::getInstance(DB_ENVIRONMENT, DB_CONNECTION);
        if (!empty($model)) {
            foreach ($column as $key => $value) {
                $this->{$key} = $value;
            }
            $this->model = $model;
        }
    }

    private function getModel()
    {
        $model = empty($this->model) ? $this:(new $this->model());
        return $model;
    }

    public function query($sql, $last_id = false){
        try {
            if(self::$enableLog) $startTime = microtime(true);
            $statement = self::$__conn->prepare($sql);
            $statement->execute();
            if(self::$enableLog) {
                $endTime = microtime(true);
                $executionTime = $endTime - $startTime;
                self::$log[] = [
                    'query' => $sql,
                    'executionTime' => "Query took " . $executionTime . " seconds to execute."
                ];
            }
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

    public static function instance()
    {
        return new Database();
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

    private function getCollection($data) {
        $collection = new Collection($data);
        return $collection;
    }

    public function get(){
        $sql = $this->sqlQuery();
        $data = $this->query($sql)->fetchAll(\PDO::FETCH_OBJ);
        if (!empty($data)) {
//            logs()->dump($this);
            $data = $this->getCollection($data)->map(fn ($item) => self::getAttribute($item));
            $data_relation = $this->workRelation($data, 'get');
            if (!empty($data_relation)) $data = $data_relation;
            return $data;
        }
        return $this->getCollection([]);
    }

    public function getArray(){
        $sql = $this->sqlQuery();
        $data = $this->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        if (!empty($data)) {
            return array_map(function ($item){
                return self::getAttribute($item, true);
            }, $data ?? []);
        }
        return [];
    }

    public function first(){
        $sql = $this->sqlQuery();
        $data = $this->query($sql)->fetch(\PDO::FETCH_OBJ);
        if (!empty($data)) {
            $data = $this->getCollection($data)->mapFirst(fn ($item) => self::getAttribute($item));
            $data_relation = $this->workRelation($data, 'first');
            if (!empty($data_relation)) $data = $data_relation;
            return $data;
        }
        return $this->getCollection(null);
    }

    public function firstArray(){
        $sql = $this->sqlQuery();
        $data = $this->query($sql)->fetch(\PDO::FETCH_ASSOC);
        if (!empty($data)) {
            return self::getAttribute($data, true);
        }
        return null;
    }

    public function findById($id) {
        $sql = $this->sqlQuery(false, null, $id);
        $data = $this->query($sql)->fetch(\PDO::FETCH_OBJ);
        if (!empty($data)) {
            $data = $this->getCollection($data)->mapFirst(fn ($item) => self::getAttribute($item));
            $data_relation = $this->workRelation($data, 'first');
            if (!empty($data_relation)) $data = $data_relation;
            return $data;
        }
        return $this->getCollection(null);
    }

    public function find($id) {
        $sql = $this->sqlQuery(false, null, $id);
        $data = $this->query($sql)->fetch(\PDO::FETCH_OBJ);
        if (!empty($data)) {
            $data = $this->getCollection($data)->mapFirst(fn ($item) => self::getAttribute($item));
            $data_relation = $this->workRelation($data, 'first');
            if (!empty($data_relation)) $data = $data_relation;
            return $data;
        }
        return $this->getCollection(null);
    }

    public function count($key = '*', $as = 'number')
    {
        $sql = $this->sqlQuery(false, "COUNT($key) as $as");
        $data = $this->query($sql)->fetch(\PDO::FETCH_OBJ);
        if (!empty($data)) {
            $data = $this->getCollection($data)->mapFirst(fn ($item) => self::getAttribute($item));
            return $data;
        }
        return $this->getCollection(null);
    }

    public function sum($key = '*', $as = '')
    {
        if(empty($as)) $as = $key;
        $sql = $this->sqlQuery(false, "SUM($key) as $as");
        $data = $this->query($sql)->fetch(\PDO::FETCH_OBJ);
        if (!empty($data)) {
            $data = $this->getCollection($data)->mapFirst(fn ($item) => self::getAttribute($item));
            return $data;
        }
        return $this->getCollection(null);
    }
}

