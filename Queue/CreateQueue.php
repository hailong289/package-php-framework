<?php
namespace Hola\Queue;
use Hola\Connection\PdoSql;
use Hola\Connection\RabbitMQ;
use Hola\Exceptions\QueueException;
use Hola\Queue\Driver\DatabaseQueueDriver;
use Hola\Queue\Driver\RabbitMQQueueDriver;
use Hola\Queue\Driver\RedisQueueDriver;
use Hola\Queue\Interface\QueueDriverInterface;
use Hola\Transport\Request;
use Hola\Transport\Response;
use Hola\Connection\Redis;
use Hola\Database\DBO;

class CreateQueue
{
    private $queue;
    private $timeout = null;
    private $connection;
    private QueueDriverInterface $driver;
    private static ?CreateQueue $instance = null;

    public function __construct(QueueDriverInterface $driver) {
        $this->driver = $driver;
        $this->queue = config('queue.queue_default');
        $this->connection = config('queue.default_connections');
    }
    
    public static function instance(): CreateQueue {
        if (is_null(self::$instance)) {
            $connectType = config('queue.default');
            $driver = self::getDriver($connectType);
            self::$instance = new CreateQueue($driver);
        }

        return self::$instance;
    }
    
    //create a function to add new element
    public function enQueue($class) {
        if (!is_object($class)) {
            throw new QueueException("Invalid class type for enQueue", 500);
        }

        if (!method_exists($class, 'handle')) {
            $className = get_class($class);
            throw new QueueException("Function handle does not exist in class $className", 500);
        }

        $dataQueue = [
            'uid' => uid(),
            'payload' => get_object_vars($class),
            'class' => addslashes($class::class),
            'queue' => $this->queue,
            'connection' => $this->connection
        ];
        
        if (!is_null($this->timeout)) {
            $dataQueue['timeout'] = $this->timeout;
        }

        $data = json_encode($dataQueue, JSON_UNESCAPED_UNICODE);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new QueueException("Failed to encode queue data: " . json_last_error_msg(), 500);
        }

        try {
            $this->driver->enqueue($this->connection, $this->queue, $data);
            $this->resetDriverDefault();
        } catch (\Throwable $e) {
            throw new QueueException($e->getMessage(), 500, $e);
        }
    }

    public function driver($name)
    {
        if (!in_array($name, ['redis','database','rabbitmq'])) {
            throw new QueueException('Driver is support redis, database, rabbitmq');
        }
        if ($this->driver->name !== $name) {
            $this->driver = self::getDriver($name);
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

    public function setTimeOut(int $timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    private static function getDriver($type)
    {
        $driver = null;
        switch ($type) {
            case 'redis':
                $driver = new RedisQueueDriver();
                break;
            case 'database':
                $driver = new DatabaseQueueDriver();
                break;
            case 'rabbitmq':
                $driver = new RabbitMQQueueDriver();
                break;
            default:
                throw new QueueException("Invalid queue connection type: $type", 500);
        }
        return $driver;
    }

    private function resetDriverDefault()
    {
        if ($this->driver->name !== config('queue.default')) {
            $this->driver = self::getDriver(config('queue.default'));
        }
    }
}