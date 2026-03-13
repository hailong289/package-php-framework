<?php

namespace Hola\Scripts\Commands\QueueJobs;
use Hola\Connection\RabbitMQ;
use Hola\Connection\Redis;
use Hola\Database\DBO;
use Hola\Database\QueryBuilder;
use Hola\Exceptions\AppException;

class SwitchDB {
    public QueueDatabase|QueueRedis|QueueRabbitmq $driver;
    private $connection_type = 'database'; // database, redis, rabbitmq

    public function __construct($name) {
        $this->connection_type = $name;
    }

    public function handle() {
        $this->switchQueueDriver();
        return $this;
    }

    protected function switchQueueDriver() {
        switch ($this->connection_type) {
            case 'database':
                $this->driver = new QueueDatabase();
                break;
            case 'redis':
                $this->driver = new QueueRedis();
                break;
            case 'rabbitmq':
                $this->driver = new QueueRabbitmq();
                break;
            default:
                throw new AppException('Driver queue not supported, please check again', 500);
        }
    }

    public function getDriver() {
        return $this->driver;
    }

    public function setQueueName($name) {
        $this->driver->setQueueName($name);
        return $this;
    }

    public function setQueueConnection($connection) {
        $this->driver->setQueueConnection($connection);
        return $this;
    }

    public function setBreakJob($break_job) {
        $this->driver->setBreakJob($break_job);
        return $this;
    }

    public function setRollBackJob($is_rollback) {
        $this->driver->setRollBackJob($is_rollback);
        return $this;
    }

}