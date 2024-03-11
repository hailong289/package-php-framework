<?php
namespace System\Queue;
use System\Core\Database;
use System\Core\Redis;

class CreateQueue
{
    private $queue = array();
    public $connection = QUEUE_WORK;
    function __construct() {}
    //create a function to add new element
    public function enQueue($class) {
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
//                    log_debug(get_class($class));
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
            } catch (\Exception $exception) {
                die($exception->getMessage());
            }
        } else {
            die('class handle does not exit');
        }
    }

    public function connection($connection = QUEUE_WORK) {
        $this->connection = $connection;
        return $this;
    }
}