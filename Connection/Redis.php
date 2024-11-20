<?php
namespace Hola\Connection;
class Redis {
    private static $conn;
    private static $instance = null;
    private static $instance_queue = null;

    public function __construct($config, $name){
        $this->connect($config, $name);
    }

    public static function instance($name = 'redis'){
        if(self::$instance == null){
            $config = config('database.connections');
            $connection = new Redis($config, $name);
            self::$instance = self::$conn;
        }
        return self::$instance;
    }

    public static function queueConnect($name = 'redis')
    {
        if(self::$instance_queue == null){
            $config = config('queue.connections');
            $connection = new Redis($config, $name);
            self::$instance_queue = self::$conn;
        }
        return self::$instance_queue;
    }

    public function connect($config, $name) {
        try {
            self::$conn = new \Redis();
            $connection = $config[$name];
            $host = $connection['host'];
            $port = $connection['port'];
            $username = $connection['username'];
            $password = $connection['password'];
            $timeout = $connection['timeout'];
            $reserved = $connection['reserved'];
            $retryInterval = $connection['retryInterval'];
            $readTimeout = $connection['readTimeout'];
            self::$conn->connect($host, $port, $timeout, $reserved, $retryInterval, $readTimeout);
            if($username && $password) {
                self::$conn->rawCommand('auth', $username, $password);
            }
        } catch (\RedisException $e) {
            throw new \RedisException("Connect redis failed. Error: ".$e->getMessage(), 500);
        } catch (\Throwable $e) {
            $mess = $e->getMessage();
            throw new \Exception("Connect redis failed. Error: $mess", 500);
        }
    }

}