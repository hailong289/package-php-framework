<?php

namespace Hola\Database;
use Hola\Connection\PdoSql;

class Connection {

    private null|\PDO $pdo = null;
    private $swithConnect = false;
    private $binndingLog = [
        'enable' => false,
        'log' => [],
        'time' => 0
    ];

    public function __construct($conn = null, $type = null) {
        $this->connect($conn, $type);
    }

    public function getPdo() {
        return $this->pdo;
    }

    public function select($sql, $binnding = [])
    {
        return $this->resolveQuery($sql, function (false|\PDOStatement $statement, bool $status) {
            return $statement->fetchAll(\PDO::FETCH_OBJ);
        }, $binnding);
    }

    public function selectOne($sql, $binnding = [])
    {
        return $this->resolveQuery($sql, function (false|\PDOStatement $statement, bool $status) {
            return $statement->fetch(\PDO::FETCH_OBJ);
        }, $binnding);
    }

    public function insert($sql, $binnding = [])
    {
        return $this->resolveQuery($sql, function (false|\PDOStatement $statement, bool $status) {
            return $status;
        }, $binnding);
    }

    public function update($sql, $binnding = [])
    {
        return $this->resolveQuery($sql, function (false|\PDOStatement $statement, bool $status) {
            return $status;
        }, $binnding);
    }

    public function insertLastId($sql, $binnding = [])
    {
        return $this->resolveQuery($sql, function (false|\PDOStatement $statement, bool $status) {
            return $this->pdo->lastInsertId();
        }, $binnding);
    }

    public function delete($sql, $binnding = [])
    {
        return $this->resolveQuery($sql, function (false|\PDOStatement $statement, bool $status) {
            return $status;
        }, $binnding);
    }

    public function query($sql)
    {
        return $this->resolveQuery($sql, function (false|\PDOStatement $statement, bool $status) {
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
        return $this->pdo->beginTransaction();
    }

    public function commit()
    {
        return $this->pdo->commit();
    }

    public function rollBack()
    {
        return $this->pdo->rollBack();
    }

    public function resolveQuery($sql, callable $callback, $bindings = [])
    {
        $logs = $this->resolveLog();
        $bindings = $this->resolveBindings($bindings);
        try {
            $statement = $this->pdo->prepare($sql);
            $status = $statement->execute($bindings);
            if ($logs instanceof \Closure) {
                $logs($sql, $bindings);
            }
            app()->event()->trigger('app.query', [
                'logs' => $this->resolveLog(true)($sql, $bindings),
                'status' => $status
            ]);
        } catch (\Throwable $e) {
            if ($logs instanceof \Closure) {
                $logs($sql, $bindings);
            }
            throw new \PDOException($e->getMessage(), 500);
        }
        return $callback($statement, $status);
    }

    public function resolveBindings($bindings = []){
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

    private function resolveLog($return = false)
    {
        if (!$this->binndingLog['enable'] && !$return) {
            return false;
        }
        $this->binndingLog['time'] = microtime(true);
        $callback = function ($sql, $bindings) use ($startTime, $return) {
            $endTime = microtime(true); // End time
            $queryTime = $endTime - $startTime; // Query time
            if ($return) {
                return [
                    'params' => $bindings,
                    'query' => $this->getRawSql($sql, $bindings),
                    'time' => $queryTime
                ];
            }
            $this->binndingLog['log'][] = [
                'params' => $bindings,
                'query' => $this->getRawSql($sql, $bindings),
                'time' => "Query took $queryTime seconds to execute."
            ];
        };
        return $callback;
    }

    private function getRawSql($sql, $bindings)
    {
        foreach ($bindings as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $val) {
                    $sql = preg_replace('/\?/', $val, $sql, 1);
                }
            } else {
                $sql = preg_replace('/\?/', $value, $sql, 1);
            }
        }
        return $sql;
    }
}