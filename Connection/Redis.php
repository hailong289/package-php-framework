<?php
namespace Hola\Connection;
use Hola\Connection\Interfaces\IConnections;
use Hola\Exceptions\ConnectionException;

class Redis implements IConnections {
    private $host;
    private $port;
    private $username;
    private $password;
    private $timeout;
    private $reserved;
    private $retryInterval;
    private $readTimeout;
    private $connection = null;

    /**
     * @param string $host
     * @param int $port
     * @param string $username
     * @param string $password
     * @param int $timeout
     * @param string|null $reserved
     * @param int $retryInterval
     * @param int $readTimeout
     */
    public function setConfig(
        $host,
        $port,
        $username,
        $password,
        $timeout = 0,
        $reserved = null,
        $retryInterval = 0,
        $readTimeout = 0
    )
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->timeout = $timeout;
        $this->reserved = $reserved;
        $this->retryInterval = $retryInterval;
        $this->readTimeout = $readTimeout;
    }

    /**
     * @return bool
     */
    public function isConnect()
    {
        return !is_null($this->connection);
    }

    /**
     * @return Redis
     */
    public function reConnect()
    {
        $this->connect();
        return $this;
    }

    /**
     * @return \Redis|null
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * @param $name
     * @param string $config_name
     * @return \Redis
     * @throws \RedisException
     * @throws \Throwable
     */
    public function connect() {
        try {
            $this->connection = new \Redis();
            $this->connection->connect(
                $this->host,
                $this->port,
                $this->timeout,
                $this->reserved,
                $this->retryInterval,
                $this->readTimeout
            );
            if($this->username && $this->password) {
                $this->connection->rawCommand('auth', $this->username, $this->password);
            }
        } catch (\RedisException $e) {
            throw new ConnectionException("Connect redis failed. Error: ".$e->getMessage(), 500);
        } catch (\Throwable $e) {
            $mess = $e->getMessage();
            throw new ConnectionException("Connect redis failed. Error: $mess", 500);
        }
    }
}