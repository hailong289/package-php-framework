<?php
namespace System\Queue;
use System\Core\Connection;
use System\Core\Database;
use System\Core\Redis;
use System\Core\Request;
use System\Core\Response;

class CreateQueue
{
    private $queue = 'jobs';
    private $timeout = 0;
    public $connection = QUEUE_WORK;
    function __construct() {}
    
    public static function instance(){
        return new CreateQueue();
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
                    if(!$redis->isConnected()) {
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

    public function connection($connection = QUEUE_WORK) {
        if($connection === 'redis' || $connection === 'database') {
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