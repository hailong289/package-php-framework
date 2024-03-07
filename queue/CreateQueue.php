<?php
namespace System\Queue;
use System\Core\Redis;

class CreateQueue
{
    private $queue = array();
    public $connection = 'redis';
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
                }
            } catch (\Exception $exception) {
                die($exception->getMessage());
            }
        } else {
            die('class handle does not exit');
        }
    }

    public function connection($connection = 'redis') {
        $this->connection = $connection;
        return $this;
    }
}