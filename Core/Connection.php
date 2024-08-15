<?php
namespace Hola\Core;
class Connection{
    private static $instance = null, $conn = null,$instance_redis = null, $redis = null,$instance_rabbitMQ = null, $rabbitMQ = null;
    private function __construct($environment = "default", $host = "mysql", $type = 1){
        $DB = cache('config', config('database'));
        $enable_debug = config_env('DEBUG_LOG_CONNECTION', false);
        if($type === 1) {
            // Ket nói database
            $db_connection = $DB[$host][$environment];
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

            }catch (\PDOException $e){
                if ($enable_debug) log_write($e,'connection');
                $mess = $e->getMessage();
                throw new \RuntimeException($mess, $e->getCode() ?? 503);
            }
        } else if($type === 2) {
            // connect redis
            try {
                self::$redis = new \Redis();
                $connection = $DB[$host][$environment];
                $host = $connection['host'];
                $port = $connection['port'];
                $username = $connection['username'];
                $password = $connection['password'];
                $timeout = $connection['timeout'];
                $reserved = $connection['reserved'];
                $retryInterval = $connection['retryInterval'];
                $readTimeout = $connection['readTimeout'];
                self::$redis->connect($host, $port, $timeout, $reserved, $retryInterval, $readTimeout);
                if($username && $password) {
                    self::$redis->rawCommand('auth', $username, $password);
                }
            }catch (\Throwable $e) {
                if ($enable_debug) log_write($e,'connection');
                throw new \RuntimeException("Connect redis failed. Error: ".$e->getMessage(), 503);
            }
        } else if ($type === 3) {
            // connect rabbit mq
            $host = config_env('RABBITMQ_HOST','localhost');
            $port = config_env('RABBITMQ_PORT',5672);
            $user = config_env('RABBITMQ_USER','guest');
            $pass = config_env('RABBITMQ_PASSWORD','guest');
            $vhost = config_env('RABBITMQ_VHOST','/');
            $scheme = config_env('RABBITMQ_SCHEME','');
            $options = config_env('RABBITMQ_OPTIONS',[
                'cafile' => null,
                'local_cert' =>null,
                'local_key' => null,
                'verify_peer' => false,
                'passphrase' => null,
            ]);
            try {
                if($scheme === "amqps") {
                    self::$rabbitMQ = new \PhpAmqpLib\Connection\AMQPSSLConnection(
                        $host,
                        $port,
                        $user,
                        $pass,
                        $vhost,
                        $options
                    );
                } else {
                    self::$rabbitMQ = new \PhpAmqpLib\Connection\AMQPStreamConnection(
                        $host,
                        $port,
                        $user,
                        $pass,
                        $vhost
                    );
                }
            } catch (\Throwable $e) {
                if ($enable_debug) log_write($e,'connection');
                throw new \RuntimeException("Connect rabbitMQ failed. Error: ".$e->getMessage(), 503);
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

    public static function instanceRabbitMQ($environment = 'default', $host ='rabbitMQ'){
        if(self::$instance_rabbitMQ == null){
            $connection_rabbitMQ = new Connection($environment, $host, 3);
            self::$instance_rabbitMQ = self::$rabbitMQ;
        }
        return self::$instance_rabbitMQ;
    }

}