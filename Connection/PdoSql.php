<?php
namespace Hola\Connection;
use Hola\Exceptions\ConnectionException;

class PdoSql {
    private static $instance = null;
    private static $instance_queue = null;

    public function __construct() {}

    /**
     * @param string|null $name
     * @return \PDO
     * @throws \Exception
     */
    public static function instance($name = null) {
        $conn_name = $name ?? config('database.default', 'mysql');
        if(self::$instance == null) {
            self::$instance = (new PdoSql())->connect($conn_name, 'database');
        }
        return self::$instance;
    }

    /**
     * @param string|null $name
     * @return \PDO
     * @throws \Exception
     */
    public static function queueConnect($name = 'database')
    {
        $conn_name = $name ?? config('queue.default', 'database');
        if(self::$instance_queue == null){
            self::$instance_queue = (new PdoSql())->connect($conn_name, 'queue');
        }
        return self::$instance_queue;
    }

    /**
     * @return bool
     */
    public static function isConnect()
    {
        return self::$instance != null;
    }

    /**
     * @return bool
     */
    public static function isConnectQueue()
    {
        return self::$instance_queue != null;
    }

    /**
     * @param $name
     * @param string $config_name
     * @return \PDO
     * @throws \Throwable
     * @throws \PDOException
     */
    public function connect($name, $config_name = 'database') {
        $config = config("$config_name.connections");
        $db_connection = $config[$name];
        $host = $db_connection['host'];
        $port = $db_connection['port'];
        $db_name = $db_connection['db_name'];
        $username = $db_connection['username'];
        $password = $db_connection['password'];
        $dsn_config = $db_connection['dsn'] ?? null;
        $driver = $db_connection['driver'] ?? 'mysql';
        try{
            // dsn configuration
            $dsn = "$driver:dbname=$db_name;host=$host;port=$port";
            if (!is_null($dsn_config)) {
                $dsn = $dsn_config;
            }
            // Configure options, - configure uft8, - configure exceptions when query fails
            $options = $db_connection['options'] ?? [];
            // connection command
            $conn = new \PDO($dsn,$username,$password,$options);
            return $conn;
        }catch (\PDOException $e){
            $mess = $e->getMessage();
            throw new ConnectionException("Connection database failed: $mess", 500);
        } catch (\Throwable $e) {
            $mess = $e->getMessage();
            throw new ConnectionException("Connection database failed: $mess", 500);
        }
    }
}