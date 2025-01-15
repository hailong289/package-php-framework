<?php
namespace Hola\Queue;
use Hola\Connection\PdoSql;
use Hola\Connection\RabbitMQ;
use Hola\Core\RedisCR;
use Hola\Exceptions\QueueException;
use Hola\Transport\Request;
use Hola\Transport\Response;
use Hola\Connection\Redis;
use Hola\Database\DBO;

class CreateQueue
{
    private $queue;
    private $timeout = 0;
    private $connection;
    private $connect_type;
    private static $instance = null;
    function __construct() {
        $this->connect_type = config('queue.default');
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
        if (!method_exists($class,'handle')) {
            $class = get_class($class);
            throw new QueueException("Function handle in class $class does not exit", 500);
        }
        try {
            $tag_queue = "queue:{$this->queue}";
            $reflectionClass = new \ReflectionClass($class);
            $data_queue = [
                'uid' => uid(),
                'payload' => get_object_vars($class),
                'class' => addslashes($reflectionClass->getName()),
                'queue' => $this->queue,
                'connection' => $this->connection,
                'timeout' => $this->timeout
            ];
            if($this->connect_type === 'redis') {
                $redis = RedisCR::workQueue($this->connection);
                RedisCR::cacheRPush($tag_queue, $data_queue, 0);
            } elseif ($this->connect_type === 'database') {
                $data = json_encode($data_queue, JSON_UNESCAPED_UNICODE);
                DBO::connection($this->connection,'queue')->from('jobs')->insert([
                    'data' => $data,
                    'queue' => $this->queue,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            } else if ($this->connect_type === 'rabbitmq') {
                $data = json_encode($data_queue, JSON_UNESCAPED_UNICODE);
                $rabbitMQ = RabbitMQ::instance($this->connection);
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
            throw new QueueException($e->getMessage(), 500);
        }
    }

    public function connection($connection) {
        $this->connection = $connection;
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