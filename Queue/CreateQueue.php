<?php
namespace Hola\Queue;
use Hola\Core\Connection;
use Hola\Core\Database;
use Hola\Core\Redis;
use Hola\Core\Request;
use Hola\Core\Response;

class CreateQueue
{
    private $queue;
    private $timeout = 0;
    private $connection;
    private static $instance = null;
    function __construct() {
        $this->connection = config('queue.default_connection');
        $this->queue = config('queue.queue_default');
        $this->timeout = config('queue.timeout');
    }
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new CreateQueue();
        }
        return self::$instance;
    }
    
    //create a function to add new element
    public function enQueue($class) {
        $is_api = (new Request())->isJson();
        $tag_queue = "queue:{$this->queue}";
        $reflectionClass = new \ReflectionClass($class);
        if (method_exists($class,'handle')) {
            try {
                $data_queue = [
                    'uid' => uid(),
                    'payload' => get_object_vars($class),
                    'class' => $reflectionClass->getShortName(),
                    'queue' => $this->queue,
                    'connection' => $this->connection,
                    'timeout' => $this->timeout
                ];
                if($this->connection === 'redis') {
                    $redis = Redis::work();
                    if (!$redis->isConnected()) {
                        throw new \RuntimeException('Redis connection is failed');
                    }
                    Redis::cacheRPush($tag_queue, $data_queue, 0);
                } elseif ($this->connection === 'database') {
                    $data = json_encode($data_queue, JSON_UNESCAPED_UNICODE);
                    Database::instance()->table('jobs')->insert([
                        'data' => $data,
                        'queue' => $this->queue,
                        'created_at' => date(' Y-m-d H:i:s')
                    ]);
                } else if ($this->connection === 'rabbitMQ') {
                    $data = json_encode($data_queue, JSON_UNESCAPED_UNICODE);
                    $rabbitMQ = Connection::instanceRabbitMQ();
                    $channel = $rabbitMQ->channel();
                    $channel->queue_declare($this->queue, false, true, false, false);
                    $attributes = [
                        'delivery_mode' => \PhpAmqpLib\Message\AMQPMessage::DELIVERY_MODE_PERSISTENT,
                        'content_type' => 'application/json',
                    ];
                    $msg = new \PhpAmqpLib\Message\AMQPMessage(
                        $data,
                        $attributes
                    );
                    $channel->basic_publish($msg, '', $this->queue);
                    // close connection
                    $channel->close();
                    $rabbitMQ->close();
                }
            } catch (\Throwable $e) {
                throw $e;
            }
        } else {
            $class = get_class($class);
            throw new \Exception("Function handle in class $class does not exit", 500);
        }
    }

    public function connection($connection) {
        if($connection === 'redis' || $connection === 'database' || $connection === 'rabbitMQ') {
            $this->connection = $connection;
        } else {
            throw new \Exception('Connection not supported', 500);
        }
        return $this;
    }

    public function setQueue($queue)
    {
        $this->queue = $queue;
        return $this;
    }

    public function setTimeOut($timeout = 0)
    {
        $this->timeout = $timeout;
        return $this;
    }
}