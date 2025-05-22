<?php
namespace Hola\Queue\Driver;
use Hola\Connection\ConnectionManager;
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
        $redis = app()
            ->get(ConnectionManager::class)
            ->setConfigName('queue')
            ->setConnectionType('redis')
            ->setConnectionName($connection)
            ->getConnection();
        $redis->rPush("queue:$queue", $data);
    }

}