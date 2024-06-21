<?php
namespace System\Core;
use System\Traits\QueryBuilder;

class Database {
    use QueryBuilder;
    private static $__conn;
    private static $enableLog = false;
    private static $log = [];
    private static $collection;
    public function __construct()
    {
        self::$__conn = Connection::getInstance(DB_ENVIRONMENT, DB_CONNECTION);
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
            $data = $this->getCollection($data)->map(fn ($item) => self::getAttribute($item));
            $data_relation = self::workRelation($data, 'get');
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
            $data_relation = self::workRelation($data, 'first');
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
        $tableName = self::$tableName ? self::$tableName:static::$tableName;
        $sql = "SELECT * FROM {$tableName} WHERE id = '$id'";
        $data = $this->query($sql)->fetch(\PDO::FETCH_OBJ);
        if (!empty($data)) {
            $data = $this->getCollection($data)->mapFirst(fn ($item) => self::getAttribute($item));
            $data_relation = self::workRelation($data, 'first');
            if (!empty($data_relation)) $data = $data_relation;
            return $data;
        }
        return $this->getCollection(null);
    }

    public function find($id) {
        $tableName = self::$tableName ? self::$tableName:static::$tableName;
        $sql = "SELECT * FROM {$tableName} WHERE id = '$id'";
        $data = $this->query($sql)->fetch(\PDO::FETCH_OBJ);
        if (!empty($data)) {
            $data = $this->getCollection($data)->mapFirst(fn ($item) => self::getAttribute($item));
            $data_relation = self::workRelation($data, 'first');
            if (!empty($data_relation)) $data = $data_relation;
            return $data;
        }
        return $this->getCollection(null);
    }
    
    public function count($key = '*', $as = 'count')
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

    public function with($name)
    {
        $instance = static::instance();
        if (is_array($name)) {
            foreach ($name as $key=>$value) {
                if(is_numeric($key)) {
                    $relation = $value;
                    if (method_exists($instance, $relation)) {
                        $instance->{$relation}();
                    }
                } else {
                    // query
                    $query = $value;
                    $relation = $key;
                    if (method_exists($instance, $relation)) {
                        $instance->{$relation}();
                        if(!empty(static::$data_relation)) {
                            $key_last = array_key_last(static::$data_relation);
                            static::$data_relation[$key_last]['query'] = $query;
                        }
                    }
                }
            }
            return $instance;
        }
        if (method_exists($instance, $name)) {
            $instance->{$name}();
        }
        return $instance;
    }

    private static function dataRelation(
        $relation,
        $model,
        $model_many,
        $foreign_key,
        $foreign_key2,
        $primary_key,
        $query
    ) {
        $instance = static::instance();
        if($relation === static::$HAS_MANY) {
            $db_table = class_exists($model) ? (new $model):Database::table($model);
            if(!empty($query)) $db_table = $query($db_table);
            $sql = $db_table->where($foreign_key, $primary_key)->clone();
            $data = $instance->query($sql)->fetchAll(\PDO::FETCH_OBJ);
            return $instance->getCollection($data)->values();
        } else if($relation === static::$BELONG_TO) {
            $db_table = class_exists($model) ? (new $model):Database::table($model);
            if(!empty($query)) $db_table = $query($db_table);
            $sql = $db_table->where($foreign_key, $primary_key)->clone();
            $data = $instance->query($sql)->fetch(\PDO::FETCH_OBJ);
            return $instance->getCollection($data)->value();
        } else if($relation === static::$MANY_TO_MANY) {
            // get id 3rd table
            $db_table_many = class_exists($model_many) ? (new $model_many):Database::table($model_many);
            $sql_tb_3rd =  $db_table_many->where($foreign_key, $primary_key)->clone();
            $data_tb_3rd = $instance->query($sql_tb_3rd)->fetchAll(\PDO::FETCH_OBJ);
            $id_join = $instance->getCollection($data_tb_3rd)->dataColumn($foreign_key2)->values();
            if(!empty($id_join)) {
                $db_table = class_exists($model) ? (new $model):Database::table($model);
                if(!empty($query)) $db_table = $query($db_table);
                $sql = $db_table->whereIn('id', $id_join)->clone();
                $data = $instance->query($sql)->fetchAll(\PDO::FETCH_OBJ);
                return $instance->getCollection($data)->values();
            }
            return [];
        } else if($relation === static::$BELONG_TO_MANY) {
            // get id 3rd table
            $db_table_many = class_exists($model_many) ? (new $model_many):Database::table($model_many);
            $sql_tb_3rd =  $db_table_many->where($foreign_key, $primary_key)->clone();
            $data_tb_3rd = $instance->query($sql_tb_3rd)->fetchAll(\PDO::FETCH_OBJ);
            $id_join = $instance->getCollection($data_tb_3rd)->dataColumn($foreign_key2)->toArray();
            if(!empty($id_join)) {
                $db_table = class_exists($model) ? (new $model):Database::table($model);
                if(!empty($query)) $db_table = $query($db_table);
                $sql = $db_table->whereIn('id', $id_join)->clone();
                $data = $instance->query($sql)->fetchAll(\PDO::FETCH_OBJ);
                return $instance->getCollection($data)->values();
            }
            return [];
        } else { // has one
            $db_table = class_exists($model) ? (new $model):Database::table($model);
            if(!empty($query)) $db_table = $query($db_table);
            $sql = $db_table->where($foreign_key, $primary_key)->clone();
            $data = $instance->query($sql)->fetch(\PDO::FETCH_OBJ);
            return $instance->getCollection($data)->value();
        }
    }

    private static function workRelation($data, $type = 'get') {
        if(empty(static::$data_relation)) {
            return false;
        }
        if($data instanceof Collection) {
            if ($type === 'get') {
                $result = $data->map(function ($item) {
                    $keys = get_object_vars($item);
                    foreach (static::$data_relation as $key => $relation) {
                        $primary_key = $relation['primary_key'];
                        $foreign_key = $relation['foreign_key'];
                        $foreign_key2 = $relation['foreign_key2'];
                        $model = $relation['model'];
                        $model_many = $relation['model_many'];
                        $name = $relation['name'];
                        $name_relation = $relation['relation'];
                        $query = $relation['query'] ?? null;
                        if (isset($keys[$primary_key])) {
                            $item->{$name} = self::dataRelation(
                                $name_relation,
                                $model,
                                $model_many,
                                $foreign_key,
                                $foreign_key2,
                                $item->{$primary_key},
                                $query
                            );
                        }
                    }
                    return $item;
                });
                static::$data_relation = []; // reset when successful
                return $result;
            } else {
                $result = $data->mapFirst(function ($item) {
                    $keys = get_object_vars($item);
                    foreach (static::$data_relation as $key => $relation) {
                        $primary_key = $relation['primary_key'];
                        $foreign_key = $relation['foreign_key'];
                        $foreign_key2 = $relation['foreign_key2'] ?? null;
                        $model = $relation['model'];
                        $model_many = $relation['model_many'] ?? null;
                        $name = $relation['name'];
                        $name_relation = $relation['relation'];
                        $query = $relation['query'] ?? null;
                        if (isset($keys[$primary_key])) {
                            $item->{$name} = self::dataRelation(
                                $name_relation,
                                $model,
                                $model_many,
                                $foreign_key,
                                $foreign_key2,
                                $item->{$primary_key},
                                $query
                            );
                        }
                    }
                    return $item;
                });
                static::$data_relation = []; // reset when successful
                return $result;
            }
        }
    }

}

