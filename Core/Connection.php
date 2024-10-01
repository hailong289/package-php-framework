<?php
namespace Hola\Core;
class Connection {
    private static $instance = null;
    private static $conn = null;
    private static $instance_redis = null;
    private static $redis = null;
    private static $instance_rabbitMQ = null;
    private static $rabbitMQ = null;
    private static $enable_debug = false;
    private function __construct($name = "mysql", $type = 1, $use_queue = false){
        if ($use_queue) {
            $DB = cache('config_queue', config('queue.connections'));
        } else {
            $DB = cache('config', config('database.connections'));
        }
        self::$enable_debug = config_env('DEBUG_LOG_CONNECTION', false);
        try {
            switch ($type) {
                case 1:
                    $this->connectDatabase($DB);
                    break;
                case 2:
                    $this->connectRedis($DB);
                    break;
                case 3:
                    $this->connectRabbitMQ($DB);
                    break;
                default:
                    break;
            }
        } catch (\Throwable $throwable) {
            if (self::$enable_debug) {
                log_write($throwable, 'connection');
            }
            throw new \RuntimeException($throwable->getMessage(), $throwable->getCode() ?? 503);
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


    // connection db
    public function connectDatabase($config) {
        $db_connection = $config[$name];
        $host = $db_connection['host'];
        $db_name = $db_connection['db_name'];
        $username = $db_connection['username'];
        $password = $db_connection['password'];
        $options = $db_connection['options'] ?? [
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        ];
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
            $mess = $e->getMessage();
            throw new \RuntimeException($mess, 503);
        }
    }

    // connection redis
    public function connectRedis($config) {
        // connect redis
        try {
            self::$redis = new \Redis();
            $connection = $config[$name];
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
            throw new \RuntimeException("Connect redis failed. Error: ".$e->getMessage(), 503);
        }
    }

    // connection rabbitMQ
    public function connectRabbitMQ($config) {
        // connect rabbit mq
        $connection = $config[$name];
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
            throw new \RuntimeException("Connect rabbitMQ failed. Error: ".$e->getMessage(), 503);
        }
    }
}