<?php
namespace Hola\Connection;
use Hola\Connection\Interfaces\IConnections;
use Hola\Exceptions\ConnectionException;

class PdoSql implements IConnections {
    private $host;
    private $port;
    private $db_name;
    private $username;
    private $password;
    private $dsn_config;
    private $driver; // mysql, pgsql, sqlite
    private $options;
    private \PDO|null $connection = null;

    public function __construct() {}

    /**
     * @param string $host
     * @param int $port
     * @param string $db_name
     * @param string $username
     * @param string $password
     * @param string|null $dsn_config
     * @param string $driver
     * @param array $options
     * @return PdoSql
     */
    public function setConfig(
        $host,
        $port,
        $db_name,
        $username,
        $password,
        $dsn_config = null,
        $driver = 'mysql',
        $options = []
    )
    {
        $this->host = $host;
        $this->port = $port;
        $this->db_name = $db_name;
        $this->username = $username;
        $this->password = $password;
        $this->dsn_config = $dsn_config;
        $this->driver = $driver;
        $this->options = $options;
        return $this;
    }

    /**
     * @return string
     */
    private function getDns()
    {
        if (!is_null($this->dsn_config)) {
            $dsn = $this->dsn_config;
        } else {
            $dsn = "$this->driver:dbname=$this->db_name;host=$this->host;port=$this->port";
        }
        return $dsn;
    }

    /**
     * @return bool
     */
    public function isConnect()
    {
        return !is_null($this->connection);
    }

    /**
     * @return $this
     * @throws \Throwable
     * @throws \PDOException
     */
    public function reConnect()
    {
        $this->connect();
        return $this;
    }

    /**
     * @return \PDO|null
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return \PDO
     * @throws \Throwable
     * @throws \PDOException
     */
    public function connect() {
        try{
            // connection command
            $this->connection = new \PDO(
                $this->getDns(),
                $this->username,
                $this->password,
                $this->options
            );
        }catch (\PDOException $e){
            $mess = $e->getMessage();
            throw new ConnectionException("Connection database failed: $mess", 500);
        } catch (\Throwable $e) {
            $mess = $e->getMessage();
            throw new ConnectionException("Connection database failed: $mess", 500);
        }
    }
}