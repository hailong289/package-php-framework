<?php
namespace Hola\Connection;
class RabbitMQ {
    private static $conn;
    private static $instance = null;
    private static $instance_queue = null;

    public function __construct($name){
        $config = cache('config', config('database.connections'));
        $this->connect($config, $name);
    }

    public static function instance($name = 'rabbitmq'){
        if(self::$instance == null){
            $connection = new RabbitMQ($name);
            self::$instance = self::$conn;
        }
        return self::$instance;
    }

    public static function queueConnect($name = 'rabbitmq')
    {
        if(self::$instance_queue == null){
            $config = cache('config_queue', config('queue.connections'));;
            $connection = self::connect($config, $name);
            self::$instance_queue = self::$conn;
        }
        return self::$instance_queue;
    }

    public function connect($config, $name) {
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
                self::$conn = new \PhpAmqpLib\Connection\AMQPSSLConnection(
                    $host,
                    $port,
                    $user,
                    $pass,
                    $vhost,
                    $options
                );
            } else {
                self::$conn = new \PhpAmqpLib\Connection\AMQPStreamConnection(
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