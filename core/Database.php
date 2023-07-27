<?php
namespace Core;

class Database {
    private $__conn;
    private $enableLog = false;
    private $log;
    public $timestamp = false;
    public $table = false;
//    use QueryBuilder;
    public function __construct()
    {
        $this->__conn = Connection::getInstance(DB_ENVIRONMENT, DB_CONNECTION);
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
            $statement = $this->__conn->prepare($sql);
            $statement->execute();
            if($this->enableLog) $this->log = $statement;
            return $statement;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function enableQueryLog(){
        $this->enableLog = true;
    }

    public function getQueryLog(){
        try {
            if(empty($this->log)){
                throw new \RuntimeException("log not working");
            }
            print_r($this->log);
            exit();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function beginTransaction(){
        return $this->__conn->beginTransaction();
    }

    public function commit(){
        return $this->__conn->commit();
    }

    public function rollBack(){
        return $this->__conn->rollBack();
    }

}

