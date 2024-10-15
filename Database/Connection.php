<?php

namespace Hola\Database;
use Hola\Connection\PdoSql;

class Connection {

    private null|\PDO $pdo = null;
    private $enableQueryLog = false;
    private $queryLog = [];
    private $swithConnect = false;
    private static $instance_queue = null;

    public function __construct($conn = null, $type = null) {
        $this->connect($conn, $type);
    }

    public function select($sql, $binnding = [])
    {
        return $this->resloveQuery($sql, function (false|\PDOStatement $statement, bool $status) {
            return $statement->fetchAll(\PDO::FETCH_OBJ);
        }, $binnding);
    }

    public function selectOne($sql, $binnding = [])
    {
        return $this->resloveQuery($sql, function (false|\PDOStatement $statement, bool $status) {
            return $statement->fetch(\PDO::FETCH_OBJ);
        }, $binnding);
    }

    public function insert($sql, $binnding = [])
    {
        return $this->resloveQuery($sql, function (false|\PDOStatement $statement, bool $status) {
            return $status;
        }, $binnding);
    }

    public function update($sql, $binnding = [])
    {
        return $this->resloveQuery($sql, function (false|\PDOStatement $statement, bool $status) {
            return $status;
        }, $binnding);
    }

    public function insertLastId($sql, $binnding = [])
    {
        return $this->resloveQuery($sql, function (false|\PDOStatement $statement, bool $status) {
            return $this->pdo->lastInsertId();
        }, $binnding);
    }

    public function delete($sql, $binnding = [])
    {
        return $this->resloveQuery($sql, function (false|\PDOStatement $statement, bool $status) {
            return $status;
        }, $binnding);
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

    public function resloveQuery($sql, callable $callback, $bindings = [])
    {
        $statement = $this->pdo->prepare($sql);
        if ($this->enableQueryLog) {
            $startTime = microtime(true); // Start time
        }
        $status = $statement->execute($this->resloveBindings($bindings));
        if ($this->enableQueryLog) {
            $endTime = microtime(true); // End time
            $queryTime = $endTime - $startTime; // Query time
            $this->queryLog[] = [
                'query' => $sql,
                'params' => $bindings,
                'time' => "Query took $queryTime seconds to execute."
            ];
        }
        return $callback($statement, $status);
    }

    public function resloveBindings($bindings = []){
        foreach ($bindings as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $bindings[] = $v;
                }
                unset($bindings[$key]);
            }
        }
        return array_values($bindings);
    }

    public function connect($connection = null, $type = null) {
        if (!is_null(self::$instance_queue)) {
            $this->pdo = self::$instance_queue;
            return $this->pdo;
        }
        if (!is_null($connection)) {
            $this->switchConnect($connection, $type);
            return $this->pdo;
        }
        if (!is_null($this->pdo)) {
            return $this->pdo;
        }
        $con = config('database.default_connection');
        $this->pdo = PdoSql::instance($con);
        return $this->pdo;
    }

    public function switchConnect($con, $type = null)
    {
        $this->swithConnect = true;
        if ($type === 'queue') {
            $this->pdo = PdoSql::queueConnect($con);
        } else {
            $this->pdo = PdoSql::instance($con);
        }
    }
}