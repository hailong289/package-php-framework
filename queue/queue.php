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
                die('class does not exit');
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