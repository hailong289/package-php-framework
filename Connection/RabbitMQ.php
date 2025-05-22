<?php
namespace Hola\Connection;
use Hola\Connection\Interfaces\IConnections;
use Hola\Exceptions\ConnectionException;

class RabbitMQ implements IConnections {
    private $host;
    private $port;
    private $username;
    private $password;
    private $vhost;
    private $scheme;
    private $options;
    private $connection = null;

    public function setConfig(
        $host,
        $port,
        $username,
        $password,
        $vhost = '/',
        $scheme = 'amqp',
        $options = []
    )
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->vhost = $vhost;
        $this->scheme = $scheme;
        $this->options = $options;
    }

    /**
     * @return bool
     */
    public function isConnect()
    {
        return !is_null($this->connection);
    }

    /**
     * @return RabbitMQ
     */
    public function reConnect() {
        if ($this->isConnect()) {
            $this->connection->close();
        }
        $this->connect();
        return $this;
    }

    /**
     * @return \PhpAmqpLib\Connection\AMQPStreamConnection|\PhpAmqpLib\Connection\AMQPSSLConnection|null
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return \PhpAmqpLib\Connection\AMQPStreamConnection
     * @throws \Throwable
     */
    public function connect() {
        try {
            if($scheme === "amqps") {
                $conn = new \PhpAmqpLib\Connection\AMQPSSLConnection(
                    $this->host,
                    $this->port,
                    $this->username,
                    $this->password,
                    $this->vhost,
                    $this->options
                );
            } else {
                $conn = new \PhpAmqpLib\Connection\AMQPStreamConnection(
                    $this->host,
                    $this->port,
                    $this->username,
                    $this->password,
                    $this->vhost
                );
            }
            $this->connection = $conn;
        } catch (\Throwable $e) {
            throw new ConnectionException("Connect rabbitMQ failed. Error: ".$e->getMessage(), 500);
        }
    }

}