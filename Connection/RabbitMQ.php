<?php
namespace Hola\Connection;
use Hola\Exceptions\ConnectionException;

class RabbitMQ {
    private static $instance = null;
    private static $instance_queue = null;

    /**
     * @param string|null $name
     * @return \PhpAmqpLib\Connection\AMQPStreamConnection
     * @throws \Exception
     */
    public static function instance($name = null){
        $conn_name = $name ?? config('queue.default', 'rabbitmq');
        if(self::$instance == null){
            $connection = (new RabbitMQ())->connect($conn_name);
            self::$instance = $connection;
        }
        return self::$instance;
    }

    /**
     * @return bool
     */
    public static function isConnect()
    {
        return self::$instance != null;
    }

    /**
     * @param $name
     * @param string $config_name
     * @return \PhpAmqpLib\Connection\AMQPStreamConnection
     * @throws \Throwable
     */
    public function connect($name, $config_name = 'queue') {
        $config = config("$config_name.connections");
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
                $conn = new \PhpAmqpLib\Connection\AMQPSSLConnection(
                    $host,
                    $port,
                    $user,
                    $pass,
                    $vhost,
                    $options
                );
            } else {
                $conn = new \PhpAmqpLib\Connection\AMQPStreamConnection(
                    $host,
                    $port,
                    $user,
                    $pass,
                    $vhost
                );
            }
            return $conn;
        } catch (\Throwable $e) {
            throw new ConnectionException("Connect rabbitMQ failed. Error: ".$e->getMessage(), 500);
        }
    }

}