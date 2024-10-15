<?php
namespace Hola\Connection;
class Redis {
    private static $conn;
    private static $instance = null;
    private static $instance_queue = null;

    public function __construct($name){
        $config = cache('config', config('database.connections'));
        $this->connect($config, $name);
    }

    public static function instance($name = 'redis'){
        if(self::$instance == null){
            $connection = new Redis($name);
            self::$instance = self::$conn;
        }
        return self::$instance;
    }

    public static function queueConnect($name = 'redis')
    {
        if(self::$instance_queue == null){
            $config = cache('config_queue', config('queue.connections'));;
            $connection = self::connect($config, $name);
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
        }catch (\RedisException $e) {
            throw new \RedisException("Connect redis failed. Error: ".$e->getMessage(), 503);
        }
    }

}