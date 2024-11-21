<?php
namespace Hola\Connection;
class Redis {
    private static $instance = null;
    private static $instance_queue = null;

    public static function instance($name = null) {
        $conn_name = $name ?? 'redis';
        if(self::$instance == null){
            self::$instance = (new Redis())->connect($conn_name, 'database');
        }
        return self::$instance;
    }

    public static function queueConnect($name = null)
    {
        $conn_name = $name ?? 'redis';
        if(self::$instance_queue == null){
            self::$instance_queue = (new Redis())->connect($conn_name, 'queue');
        }
        return self::$instance_queue;
    }

    public static function isConnect()
    {
        return self::$instance != null;
    }

    public static function isConnectQueue()
    {
        return self::$instance_queue != null;
    }

    public function connect($name, $config_name = 'database') {
        try {
            $config = config("$config_name.connections");
            $conn = new \Redis();
            $connection = $config[$name];
            $host = $connection['host'];
            $port = $connection['port'];
            $username = $connection['username'];
            $password = $connection['password'];
            $timeout = $connection['timeout'];
            $reserved = $connection['reserved'];
            $retryInterval = $connection['retryInterval'];
            $readTimeout = $connection['readTimeout'];
            $conn->connect($host, $port, $timeout, $reserved, $retryInterval, $readTimeout);
            if($username && $password) {
                $conn->rawCommand('auth', $username, $password);
            }
            return $conn;
        } catch (\RedisException $e) {
            throw new \RedisException("Connect redis failed. Error: ".$e->getMessage(), 500);
        } catch (\Throwable $e) {
            $mess = $e->getMessage();
            throw new \Exception("Connect redis failed. Error: $mess", 500);
        }
    }
}