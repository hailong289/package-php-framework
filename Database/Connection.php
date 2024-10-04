<?php

namespace Hola\Database;

class Connection {

    private null|\PDO $pdo = null;
    private $enableQueryLog = false;
    private $queryLog = [];
    private $swithConnect = false;

    public function __construct($conn = null) {
        $this->connect($conn);
    }

    public function select($sql)
    {
        return $this->resloveQuery($sql, function (false|\PDOStatement $statement, bool $status) {
            return $statement->fetchAll(\PDO::FETCH_OBJ);
        });
    }

    public function selectOne($sql)
    {
        return $this->resloveQuery($sql, function (false|\PDOStatement $statement, bool $status) {
            return $statement->fetch(\PDO::FETCH_OBJ);
        });
    }

    public function insert($sql)
    {
        return $this->resloveQuery($sql, function (false|\PDOStatement $statement, bool $status) {
            return $status;
        });
    }

    public function update($sql)
    {
        return $this->resloveQuery($sql, function (false|\PDOStatement $statement, bool $status) {
            return $status;
        });
    }

    public function insertLastId($sql)
    {
        return $this->resloveQuery($sql, function (false|\PDOStatement $statement, bool $status) {
            return $this->pdo->lastInsertId();
        });
    }

    public function delete($sql)
    {
        return $this->resloveQuery($sql, function (false|\PDOStatement $statement, bool $status) {
            return $status;
        });
    }

    public function query($sql)
    {
        return $this->resloveQuery($sql, function (false|\PDOStatement $statement, bool $status) {
            return $statement;
        });
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
        if ($this->swithConnect) {
            $this->pdo = null;
            $this->swithConnect = false;
        }
        return $callback($statement, $status);
    }

    public function connect($connection = null) {
        if (!is_null($connection)) {
            $this->switchConnect($connection);
            return $this->pdo;
        }
        if (!is_null($this->pdo)) {
            return $this->pdo;
        }
        $con = config('database.default_connection');
        $this->pdo = \Hola\Core\Connection::getInstance($con);
        return $this->pdo;
    }

    public function switchConnect($con)
    {
        $this->swithConnect = true;
        $this->pdo = \Hola\Core\Connection::getInstance($con);
    }

}