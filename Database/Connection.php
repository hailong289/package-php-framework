<?php

namespace Hola\Database;

class Connection {

    private null|\PDO $pdo = null;
    private $enableQueryLog = false;
    private $queryLog = [];

    public function __construct($conn = null) {
        $this->connect($conn);
    }

    public function select($sql)
    {
        return $this->resloveQuery($sql, function (false|\PDOStatement $statement, bool $status) {
            return $statement->fetchAll();
        })();
    }

    public function selectOne($sql)
    {
        return $this->resloveQuery($sql, function (false|\PDOStatement $statement, bool $status) {
            return $statement->fetch();
        })();
    }

    public function insert($sql)
    {
        return $this->resloveQuery($sql, function (false|\PDOStatement $statement, bool $status) {
            return $status;
        })();
    }

    public function insertLastId($sql)
    {
        return $this->resloveQuery($sql, function (false|\PDOStatement $statement, bool $status) {
            return $this->pdo->lastInsertId();
        })();
    }

    public function update($sql)
    {
        return $this->resloveQuery($sql, function (false|\PDOStatement $statement, bool $status) {
            return $status;
        })();
    }

    public function delete($sql)
    {
        return $this->resloveQuery($sql, function (false|\PDOStatement $statement, bool $status) {
            return $status;
        })();
    }

    public function query($sql)
    {
        return $this->resloveQuery($sql, function (false|\PDOStatement $statement, bool $status) {
            return $statement;
        })();
    }

    public function enableQueryLog()
    {
        $this->enableQueryLog = true;
        return $this->enableQueryLog;
    }

    public function getQueryLog()
    {
        return $this->queryLog;
    }

    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    public function commit()
    {
        $this->pdo->commit();
    }

    public function rollBack()
    {
        $this->pdo->rollBack();
    }

    public function resloveQuery($sql, callable $callback, $binnding = [])
    {
        $statement = $this->pdo->prepare($sql);
        if ($this->enableQueryLog) {
            $startTime = microtime(true); // Start time
        }

        $status = $statement->execute();
        if ($this->enableQueryLog) {
            $endTime = microtime(true); // End time
            $queryTime = $endTime - $startTime; // Query time
            $this->queryLog[] = [
                'query' => $sql,
                'time' => "Query took $queryTime seconds to execute."
            ];
        }
        return $callback($statement, $status);
    }

    public function connect($connection = null) {
        $con = $connection ?? config('database.default_connection');
        if (!is_null($this->pdo)) {
            return $this->pdo;
        }
        $this->pdo = \Hola\Core\Connection::getInstance($con);
    }

}