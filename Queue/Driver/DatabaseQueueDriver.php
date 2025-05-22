<?php
namespace Hola\Queue\Driver;
use Hola\Connection\ConnectionManager;
use Hola\Queue\Interface\QueueDriverInterface;
use Hola\Database\DBO;


class DatabaseQueueDriver implements QueueDriverInterface
{
    public $name;
    
    public function __construct() {
        $this->name = 'database';
    }
    
    public function enqueue(string $connection, string $queue, string $data): void
    {
        app()->get(ConnectionManager::class)->setConfigName('queue');
        DBO::connection($connection)
            ->from('jobs')
            ->insert([
                'data' => $data,
                'queue' => $queue,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
    }
}