<?php
namespace Hola\Core;
class Connection{
    private static $instance = null, $conn = null,$instance_redis = null, $redis = null,$instance_rabbitMQ = null, $rabbitMQ = null;
    private function __construct($name = "mysql", $type = 1, $use_queue = false){
        if ($use_queue) {
            $DB = cache('config_queue', config('queue.connections'));
        } else {
            $DB = cache('config', config('database.connections'));
        }
        $enable_debug = config_env('DEBUG_LOG_CONNECTION', false);
        if($type === 1) {
            // Ket nói database
            $db_connection = $DB[$name];
            $host = $db_connection['host'];
            $db_name = $db_connection['db_name'];
            $username = $db_connection['username'];
            $password = $db_connection['password'];
            $options = $db_connection['options'];
            try{
                //    cấu hình dsn
                $name = $name === 'database' ? 'mysql' : $name;
                $dsn = "$name:dbname=$db_name;host=$host";
                //    Cấu hình option, - cấu hình uft8, - cấu hình ngoại lệ khi truy vấn bị lỗi
                $options = $options ?? [
                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                ];
                // cấu lệnh kết nối
                $conn = new \PDO($dsn,$username,$password,$options);
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
                $connection = $DB[$name];
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
            $connection = $DB[$name];
            $host = $connection['host'];
            $port = $connection['port'];
            $user = $connection['username'];
            $pass = $connection['password'];
            $vhost = $connection['vhost'];
            $scheme = $connection['scheme'];
            $options = $connection['options'];
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
    public static function getInstance($name = 'mysql', $queue = false){
        if(self::$instance == null){
            $connection = new Connection($name, 1, $queue);
            self::$instance = self::$conn;
        }
        return self::$instance;
    }

    public static function getInstanceRedis($name = 'redis', $queue = false){
        if(self::$instance_redis == null){
            $connection_redis = new Connection($name, 2, $queue);
            self::$instance_redis = self::$redis;
        }
        return self::$instance_redis;
    }

    public static function instanceRabbitMQ($name = 'rabbitmq', $queue = false){
        if(self::$instance_rabbitMQ == null){
            $connection_rabbitMQ = new Connection($name, 3, $queue);
            self::$instance_rabbitMQ = self::$rabbitMQ;
        }
        return self::$instance_rabbitMQ;
    }

}