<?php

namespace Hola\Database;
use Hola\Connection\PdoSql;

class Connection {

    private null|\PDO $pdo = null;
    private $enableQueryLog = false;
    private $queryLog = [];
    private $swithConnect = false;
    private $binndingLog = [
        'enable' => false,
        'log' => [],
        'time' => 0
    ];

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
        $this->binndingLog['enable'] = true;
        return true;
    }

    public function getQueryLog()
    {
        return $this->binndingLog['log'];
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
        $logs = $this->resloveLog();
        try {
            $statement = $this->pdo->prepare($sql);
            $status = $statement->execute($this->resloveBindings($bindings));
            if ($logs instanceof \Closure) {
                $logs($sql, $bindings);
            }
        } catch (\Throwable $e) {
            if ($logs instanceof \Closure) {
                $logs($sql, $bindings);
            }
            log_write($e, 'application');
            throw $e;
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
        if (!is_null($connection)) {
            return $this->switchConnect($connection, $type);
        }
        if (!is_null($this->pdo)) {
            return $this->pdo;
        }
        $this->pdo = PdoSql::instance();
        return $this->pdo;
    }

    public function switchConnect($con, $type = null)
    {
        $this->swithConnect = true;
        if ($type === 'queue') {
            $this->pdo = (new PdoSql())->connect($con, 'queue');
        } else {
            $this->pdo = (new PdoSql())->connect($con, 'database');
        }
        return $this->pdo;
    }

    private function resloveLog()
    {
        if (!$this->binndingLog['enable']) {
            return false;
        }
        $this->binndingLog['time'] = microtime(true);
        $callback = function ($sql, $bindings) use ($startTime) {
            $endTime = microtime(true); // End time
            $queryTime = $endTime - $startTime; // Query time
            $sqlRaw = $sql;
            foreach ($bindings as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $val) {
                        $sqlRaw = preg_replace('/\?/', $val, $sqlRaw);
                    }
                } else {
                    $sqlRaw = preg_replace('/\?/', $value, $sqlRaw);
                }
            }
            $this->queryLog[] = [
                'query' => $sql,
                'params' => $bindings,
                'query_raw' => $sqlRaw,
                'time' => "Query took $queryTime seconds to execute."
            ];
        };
        return $callback;
    }
}