<?php

namespace Hola\Database;
use Hola\Connection\ConnectionManager;
use Hola\Connection\PdoSql;

class QueryConnectBuilder {

    private null|\PDO $pdo = null;
    private $swithConnect = false;
    private $binndingLog = [
        'enable' => false,
        'log' => [],
        'time' => 0
    ];

    public function __construct($conn = null, $type = 'database') {
        if ($type === 'queue') {
            $this->queueConnectDB($conn);
            return;
        }
        $this->connect($conn);
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
                'type' => 'event_query',
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

    public function connect($connection = null) {
        if (!is_null($connection)) {
            $this->pdo = app()
                ->get(ConnectionManager::class)
                ->setConnectionType('database')
                ->setConnectionName($connection)
                ->getConnection();
        }

        if (!is_null($this->pdo)) {
            return $this->pdo;
        }

        $this->pdo = app()
            ->get(ConnectionManager::class)
            ->setConnectionType('database')
            ->setConnectionName(config('database.default'))
            ->getConnection();

        return $this->pdo;
    }

    public function queueConnectDB($connection)
    {

        if (!is_null($this->pdo)) {
            return $this->pdo;
        }

        $this->pdo = app()
            ->get(ConnectionManager::class)
            ->setConfigName('queue')
            ->setConnectionType('database')
            ->setConnectionName($connection)
            ->getConnection();

        return $this->pdo;
    }

    private function resolveLog($return = false)
    {
        if (!$this->binndingLog['enable'] && !$return) {
            return false;
        }
        $this->binndingLog['time'] = microtime(true);
        $callback = function ($sql, $bindings) use ($return) {
            $this->binndingLog['time'] = microtime(true) - $this->binndingLog['time']; // Query time
            if ($return) {
                return [
                    'params' => $bindings,
                    'query' => $this->getRawSql($sql, $bindings),
                    'time' => $this->binndingLog['time']
                ];
            }
            $this->binndingLog['log'][] = [
                'params' => $bindings,
                'query' => $this->getRawSql($sql, $bindings),
                'time' => "Query took {$this->binndingLog['time']} seconds to execute."
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