<?php
namespace Hola\Connection;
class RabbitMQ {
    private static $instance = null;
    private static $instance_queue = null;

    public static function instance($name = null){
        $conn_name = $name ?? config('queue.default', 'rabbitmq');
        if(self::$instance == null){
            $connection = (new RabbitMQ())->connect($conn_name);
            self::$instance = self::$conn;
        }
        return self::$instance;
    }

    public static function isConnect()
    {
        return self::$instance != null;
    }

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
            throw new \RuntimeException("Connect rabbitMQ failed. Error: ".$e->getMessage(), 500);
        }
    }

}