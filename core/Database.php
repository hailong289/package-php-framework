<?php
namespace Core;
use Core\Builder\QueryBuilder;

class Database {
    private static $__conn;
    private static $enableLog = false;
    private static $log;
    private static $class;
    use QueryBuilder;
    public function __construct()
    {
        self::$__conn = Connection::getInstance(DB_ENVIRONMENT, DB_CONNECTION);
        self::$class = $this;
    }

//    public function insert($data){
//        if(!empty($data)){
//            $field = '';
//            $value = '';
//            // print_r($data);
//            foreach($data as $key=>$val){
//                $field .= $key . ',';
//                $value .= "'".$val."'". ",";
//            }
//            if($this->timestamp){
//                $field .= 'created_at';
//                $value .= "'".date('Y-m-d H:i:s')."'";
//            }
//
//            $field = rtrim($field, ',');
//            $value = rtrim($value, ',');
//            $sql = "INSERT INTO $this->table($field) VALUES ($value)";
//            $status = $this->query($sql);
//            if($status){
//                return true;
//            }
//            return false;
//        }
//    }
//
//    public function update($data, $id){
//        if(!empty($data)){
//            $field = '';
//            $value = '';
//            $compare = '';
//            foreach($data as $key=>$val){
//                // $field .= $key .',';
//                // $value .= "'".$val."'".",";
//                $compare .= $key." = '".$val."', ";
//            }
//
//            if($this->timestamp){
//                $field .= 'updated_at';
//                $value .= "'".date('Y-m-d H:i:s')."'";
//            }
//
//            $compare = rtrim($compare, ", ");
//            $sql = "UPDATE {$this->table} SET {$compare} WHERE id = {$id}";
//            $status = $this->query($sql);
//            if($status){
//                return true;
//            }
//            return false;
//        }
//    }
//
    public function query($sql){
        try {
            $statement = self::$__conn->prepare($sql);
            $statement->execute();
            if(self::$enableLog) self::$log = $statement;
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
            print_r(self::$log);
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

}

