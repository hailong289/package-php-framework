<?php

namespace Hola\Connection;

use Hola\Exceptions\AppException;

class ConnectionManager {
    protected $connectionName = null; // default connection name
    protected $connectionType = 'database'; // Redis, RabbitMQ, database
    protected $connection = null; // default connection
    protected $configName = 'database'; // only for database, queue
    protected $configs = []; // default config name

    public function __construct()
    {
        app()->singletonIf(PdoSql::class, function () {
            return new PdoSql();
        });
        app()->singletonIf(Redis::class, function () {
            return new Redis();
        });
        app()->singletonIf(RabbitMQ::class, function () {
            return new RabbitMQ();
        });
    }

    public function setConnectionName($name)
    {
        $this->connectionName = $name;
        return $this;
    }

    public function setConnectionType($type)
    {
        $this->connectionType = $type;
        return $this;
    }

    public function setConfigName($name)
    {
        $this->configName = $name;
        return $this;
    }

    public function resetConfigName()
    {
        $this->configName = 'database';
        return $this;
    }

    public function getConfigName()
    {
        return $this->configName;
    }

    public function getConnectionName()
    {
        return $this->connectionName;
    }

    private function resolveConfig()
    {
        $this->configs = config("{$this->getConfigName()}.connections.{$this->getConnectionName()}");
    }


    public function getConnection()
    {
        if (is_null($this->connection)) {
            $this->handle();
        }
        return $this->connection->getConnection();
    }

    /**
     * @return PdoSql|Redis|RabbitMQ|MongoDB
     * @throws AppException
     */
    public function handle()
    {
        $this->resolveConfig();
        switch ($this->connectionType) {
            case 'database':
                $this->connection = app()->get(PdoSql::class);
                $this->connection->setConfig(
                    $this->configs['host'],
                    $this->configs['port'],
                    $this->configs['db_name'],
                    $this->configs['username'],
                    $this->configs['password'],
                    $this->configs['dsn_config'] ?? null,
                    $this->configs['driver'] ?? 'mysql',
                    $this->configs['options'] ?? []
                );
                $this->connection->connect();
                break;
            case 'redis':
                $this->connection = app()->get(Redis::class);
                $this->connection->setConfig(
                    $this->configs['host'],
                    $this->configs['port'],
                    $this->configs['username'],
                    $this->configs['password'],
                    $this->configs['timeout'] ?? 0,
                    $this->configs['reserved'] ?? null,
                    $this->configs['retryInterval'] ?? 0,
                    $this->configs['readTimeout'] ?? 0
                );
                $this->connection->connect();
                break;
            case 'rabbitmq':
                $this->connection = app()->get(RabbitMQ::class);
                $this->connection->setConfig(
                    $this->configs['host'],
                    $this->configs['port'],
                    $this->configs['username'],
                    $this->configs['password'],
                    $this->configs['vhost'] ?? '/',
                    $this->configs['scheme'] ?? 'amqp',
                    $this->configs['options'] ?? []
                );
                $this->connection->connect();
                break;
            case 'mongodb':
                $this->connection = app()->get(MongoDB::class);
                $this->connection->setConfig(
                    $this->configs['host'],
                    $this->configs['port'],
                    $this->configs['db_name'],
                    $this->configs['username'],
                    $this->configs['password'],
                    $this->configs['dsn'] ?? null,
                    $this->configs['driver'] ?? 'mongodb'
                );
                $this->connection->connect();
                break;
            default:
                throw new AppException("Connection type {$this->connectionType} not supported");
        }
    }

}