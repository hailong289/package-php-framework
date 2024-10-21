<?php
namespace Hola\Connection;
class PdoSql {
    private static $conn;
    private static $instance = null;
    private static $instance_queue = null;

    public function __construct($name){
        $config = cache('config', config('database.connections'));
        $this->connect($config, $name);
    }

    public static function instance($name = 'mysql'){
        if(self::$instance == null){
            $connection = new PdoSql($name);
            self::$instance = self::$conn;
        }
        return self::$instance;
    }

    public static function queueConnect($name = 'database')
    {
        if(self::$instance_queue == null){
            $config = cache('config_queue', config('queue.connections'));;
            $connection = self::connect($config, $name);
            self::$instance_queue = self::$conn;
        }
        return self::$instance_queue;
    }

    public function connect($config, $name) {
        $db_connection = $config[$name];
        $host = $db_connection['host'];
        $db_name = $db_connection['db_name'];
        $username = $db_connection['username'];
        $password = $db_connection['password'];
        $dsn_config = $db_connection['dsn'] ?? null;
        $driver = $db_connection['driver'] ?? 'mysql';
        try{
            // dsn configuration
            $dsn = "$driver:dbname=$db_name;host=$host";
            if (!is_null($dsn_config)) {
                $dsn = $dsn_config;
            }
            // Configure options, - configure uft8, - configure exceptions when query fails
            $options = $db_connection['options'] ?? [
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ];
            // connection command
            $conn = new \PDO($dsn,$username,$password,$options);
            self::$conn = $conn;
        }catch (\PDOException $e){
            $mess = $e->getMessage();
            throw new \PDOException("Connection database failed: $mess", 503);
        }
    }

}