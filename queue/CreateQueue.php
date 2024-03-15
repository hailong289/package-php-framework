<?php
namespace System\Queue;
use System\Core\Database;
use System\Core\Redis;
use System\Core\Request;
use System\Core\Response;

class CreateQueue
{
    private $queue = array();
    public $connection = QUEUE_WORK;
    function __construct() {}
    //create a function to add new element
    public function enQueue($class) {
        $is_api = (new Request())->isJson();
        $tag_queue = 'queue:job';
        if (method_exists($class,'handle')) {
            try {
                if($this->connection === 'redis') {
                    $redis = Redis::work();
                    if(!$redis->isConnected()) {
                        throw new \RuntimeException('Redis connection is failed');
                    }
                    Redis::cacheRPush($tag_queue, [
                        'uid' => uid(),
                        'payload' => $class,
                        'class' => get_class($class)
                    ], 0);
                } elseif ($this->connection === 'database') {
                    $data = json_encode([
                        'uid' => uid(),
                        'payload' => get_object_vars($class),
                        'class' => str_replace('\\','/',get_class($class))
                    ], JSON_UNESCAPED_UNICODE);
                    Database::table('jobs')->insert([
                        'queue' => $data,
                        'created_at' => date(' Y-m-d H:i:s')
                    ]);
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
                "message" => "Class handle in $class does not exit",
                "code" => 500,
            ]);
            exit();
        }
    }

    public function connection($connection = QUEUE_WORK) {
        $this->connection = $connection;
        return $this;
    }
}