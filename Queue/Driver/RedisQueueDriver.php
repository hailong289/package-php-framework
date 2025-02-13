<?php
namespace Hola\Queue\Driver;
use Hola\Queue\Interface\QueueDriverInterface;
use Hola\Connection\Redis;

class RedisQueueDriver implements QueueDriverInterface
{
    public $name;

    public function __construct() {
        $this->name = 'redis';
    }
    
    public function enqueue(string $connection, string $queue, string $data): void
    {
        $redis = Redis::queueConnect($connection);
        $redis->rPush("queue:$queue", $data);
    }

}