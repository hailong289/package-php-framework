<?php
namespace App\Core;
class Connection{
    private static $instance = null, $conn = null,$instance_redis = null, $redis = null;
    private function __construct($environment = "default", $host = "mysql", $type = 1){
        if($type == 1) {
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
                throw new \RuntimeException($mess, $e->getCode() ?? 503);
            }
        } else if($type == 2) {
            // connect redis
            try {
                self::$redis = new \Redis();
                $connection = DATABASE[$host][$environment];
                $host = $connection['host'];
                $port = $connection['port'];
                $timeout = $connection['timeout'];
                $reserved = $connection['reserved'];
                $retryInterval = $connection['retryInterval'];
                $readTimeout = $connection['readTimeout'];
                self::$redis->connect($host, $port, $timeout, $reserved, $retryInterval, $readTimeout);
            }catch (\Throwable $e) {
                throw new \RuntimeException("Connect redis failed ".$e->getMessage(), 503);
            }
        }
    }
    public static function getInstance($environment = 'default', $host = 'mysql'){
        if(self::$instance == null){
            $connection = new Connection($environment, $host);
            self::$instance = self::$conn;
        }
        return self::$instance;
    }

    public static function getInstanceRedis($environment = 'default', $host = 'redis'){
        if(self::$instance_redis == null){
            $connection_redis = new Connection($environment, $host, 2);
            self::$instance_redis = self::$redis;
        }
        return self::$instance_redis;
    }

}