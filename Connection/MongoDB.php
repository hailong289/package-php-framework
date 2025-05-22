<?php
namespace Hola\Connection;
use Hola\Connection\Interfaces\IConnections;
use Hola\Exceptions\ConnectionException;

class MongoDB implements IConnections {
    private $host;
    private $port;
    private $db_name;
    private $username;
    private $password;
    private $dsn;
    private $driver; // mongodb, mongodb+srv
    private $connection = null;

    public function setConfig(
        $host,
        $port,
        $db_name,
        $username,
        $password,
        $dsn = null,
        $driver = 'mongodb'
    )
    {
        $this->host = $host;
        $this->port = $port;
        $this->db_name = $db_name;
        $this->username = $username;
        $this->password = $password;
        $this->dsn = $dsn;
        $this->driver = $driver;
        return $this;
    }

    /**
     * @return string
     */
    public function getDns() {
        if (!is_null($this->dsn)) {
            $dsn = $this->dsn;
        } else {
            $dsn = "$this->driver://{$this->username}:{$this->password}@$this->host:$this->port/{$this->db_name}";
        }
        return $dsn;
    }

    /**
     * @return bool
     */
    public function isConnect() {
        return !is_null($this->connection);
    }

    /**
     * @return void
     */
    public function reConnect() {
        $this->connect();
        return $this;
    }

    /**
     * @return \MongoDB\Database|null
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * @return void
     * @throws \MongoDB\Driver\Exception\Exception
     * @throws \Throwable
     */
    public function connect() {
        try{
            $client = new \MongoDB\Client($this->getDns());
            $this->connection = $client->{$this->db_name};
        } catch (\MongoDB\Driver\Exception\Exception $e) {
            $mess = $e->getMessage();
            throw new ConnectionException("MongoDB connection failed: $mess", 500);
        } catch (\Throwable $e) {
            $mess = $e->getMessage();
            throw new ConnectionException("Connection failed: $mess", 500);
        }
    }

}