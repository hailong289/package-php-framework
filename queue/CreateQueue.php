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
                    Database::table('jobs')->insert([
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
                        'delivery_mode' => 2,
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
            } catch (\Exception $e) {
                $code = (int)$e->getCode();
                if($is_api) {
                    echo json_encode([
                        "message" => $e->getMessage(),
                        "code" => $code,
                        "line" => $e->getLine(),
                        "file" => $e->getFile(),
                        "trace" => $e->getTraceAsString()
                    ]);
                    exit();
                }
                Response::view("error.index", [
                    "message" => $e->getMessage(),
                    "code" => $code,
                    "line" => $e->getLine(),
                    "file" => $e->getFile(),
                    "trace" => $e->getTraceAsString()
                ]);
                exit();
            }
        } else {
            if($is_api) {
                echo json_encode([
                    "message" => "Class handle in $class does not exit",
                    "code" => 500
                ]);
                exit();
            }
            Response::view("error.index", [
                "message" => "Function handle in class $class does not exit",
                "code" => 500,
            ]);
            exit();
        }
    }

    public function connection($connection = QUEUE_WORK) {
        if($connection === 'redis' || $connection === 'database') {
            $this->connection = $connection;
        } else {
            $is_api = (new Request())->isJson();
            if($is_api) {
                echo json_encode([
                    "message" => "Connection not supported",
                    "code" => 500
                ]);
                exit();
            }
            Response::view("error.index", [
                "message" => "Connection not supported",
                "code" => 500,
            ]);
            exit();
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