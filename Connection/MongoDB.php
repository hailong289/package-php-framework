<?php
namespace Hola\Connection;
class MongoDB {
    private static $instance = null;

    public static function instance($name = null){
        $conn_name = $name ?? config('database.default', 'mongodb');
        if(self::$instance == null){
            $connection = (new MongoDB())->connect($conn_name);
            self::$instance = self::$conn;
        }
        return self::$instance;
    }

    public function connect($name, $config_name = 'database'){
        $config = config("$config_name.connections");
        $db_connection = $config[$name];
        $host = $db_connection['host'];
        $port = $db_connection['port'];
        $db_name = $db_connection['db_name'];
        $username = $db_connection['username'];
        $password = $db_connection['password'];
        $dsn_config = $db_connection['dsn'] ?? null;
        $driver = $db_connection['driver'] ?? 'mongodb';
        try{
            // dsn configuration
            $dsn = "$driver://{$username}:{$password}@$host:$port/{$db_name}";
            if (!is_null($dsn_config)) {
                $dsn = $dsn_config;
            }
            $client = new \MongoDB\Client($dsn);
            self::$conn = $client->{$db_name};
        } catch (\MongoDB\Driver\Exception\Exception $e) {
            $mess = $e->getMessage();
            throw new \Exception("MongoDB connection failed: $mess", 500);
        } catch (\Throwable $e) {
            $mess = $e->getMessage();
            throw new \Exception("Connection failed: $mess", 500);
        }
    }

}