<?php
namespace Core;
class Connection{
    private static $instance = null, $conn = null;
    private function __construct($environment = "default", $host = "mysql"){
        // Ket nói database
        $db_connection = DATABASE[$host][$environment];
        try{
            //    cấu hình dsn
            $dsn = "$host:dbname=".$db_connection['DATABASE_NAME'].";host=".$db_connection['HOST'];
            //    Cấu hình option, - cấu hình uft8, - cấu hình ngoại lệ khi truy vấn bị lỗi
            $options = [
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ];
            // cấu lệnh kết nối
            $conn = new \PDO($dsn,$db_connection['USERNAME'],$db_connection['PASSWORD'],$options);
            self::$conn = $conn;


        }catch (PDOException $e){
            $mess = $e->getMessage();
            die($mess);
        }
    }
    public static function getInstance($environment = 'default', $host = 'mysql'){
        if(self::$instance == null){
            $connection = new Connection($environment, $host);
            self::$instance = self::$conn;
        }
        return self::$instance;
    }

}