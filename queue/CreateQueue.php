<?php
namespace Queue;
use App\Core\Redis;

class CreateQueue
{
    public $queue = array();
    public $connection = 'redis';
    function __construct() {}
    //create a function to add new element
    public function enQueue($class) {
        $tag_queue = 'queue:job';
        if (method_exists($class,'handle')) {
            try {
                if($this->connection === 'redis') {
                   $redis = Redis::work();
                   Redis::cacheRPush($tag_queue, [
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

    public function deQueue($class) {
        $redis = Redis::work();
        $redis->del('queue:job');
    }

    public function deQueueList($value, $index) {
        $redis = Redis::work();
        $redis->lRem('queue:job', $value, $index);
    }

    public function connection($connection = 'redis') {
        $this->connection = $connection;
        return $this;
    }
}