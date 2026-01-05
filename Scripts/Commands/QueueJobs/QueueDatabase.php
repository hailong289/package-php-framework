<?php

namespace Hola\Scripts\Commands\QueueJobs;

use Hola\Connection\ConnectionManager;
use Hola\Database\DBO;
use Hola\Database\QueryBuilder;
use Hola\Exceptions\QueueException;

class QueueDatabase {
    public $queueName = 'default';
    public $queueConnection = 'mysql';
    public $queueConnectionType = 'database';
    private $break_job = false;
    private QueryBuilder|null $driver = null;
    private $is_rollback = false;
    private $table = 'jobs';

    private function data($data)
    {
        return [
            'key' => $data['key'] ?? $data['id'] ?? 0,
            'uid' => $data['uid'],
            'class' => stripslashes($data['class']),
            'payload' => $data['payload'],
            'timeout' => $data['timeout'] ?? 0,
        ];
    }

    public function setQueueName($name)
    {
        $this->queueName = $name;
        return $this;
    }

    public function setQueueConnection($connection)
    {
        $this->queueConnection = $connection;
        return $this;
    }

    public function setBreakJob($break_job)
    {
        $this->break_job = $break_job;
        return $this;
    }

    public function setRollBackJob($is_rollback)
    {
        $this->is_rollback = $is_rollback;
        return $this;
    }

    public function connect()
    {
        $this->driver = (new QueryBuilder())->queueConnection($this->queueConnection);
        return $this;
    }

    public function getQueue()
    {
        if (is_null($this->driver)) {
            $this->connect();
        }

        if ($this->is_rollback) {
            $this->table = 'failed_jobs';
            $this->queueName = 'failed_jobs';
        }

        $data = $this->driver
            ->from($this->table)
            ->where('queue', $this->queueName)
            ->limit(1)
            ->first()?->toArray();

        if (empty($data)) {
            return null;
        }

        $queueData = json_decode($data['data'], true);
        $queueData['key'] = $data['id'];
        $queueData = $this->data($queueData);
        $this->driver->from($this->table)
            ->where('queue', $this->queueName)
            ->where('id', $queueData['key'])
            ->delete();
        return $queueData;
    }

    public function queueWork(QueueManage $queueManage)
    {
        $queueManage->eventTimeOut(fn ($payload) => $this->eventTimeOut($payload, $queueManage));
        while ($queue = $this->getQueue()) {
            if ($this->break_job) {
                break;
            }
            sleep(1);
            $taskName = $queue['class'] . '_' . uid();
            if (!empty($queue['timeout'])) {
                $queueManage->setTimeOut((int)$queue['timeout']);
            }

            $callback = function () use ($queue, $queueManage) {
                $queueManage->pendingJob();
                try {
                    if (!method_exists($queue['class'], 'handle')) {
                        throw new QueueException("function handle does not exits in {$queue['class']}");
                    }
                    app()->make($queue['class'], $queue['payload'])->handle();
                    $queueManage->doneJob();
                } catch (\Throwable $exception) {
                    $data = $this->getDataFailed($queue, $exception);
                    $queueManage->failedJob($this, $data, $exception);
                }
            };
            $queueManage->execute($taskName, $callback, $queue);
        }
    }

    private function eventTimeOut($payload, QueueManage $queueManage)
    {
        $taskName = $payload['taskName'];
        $error = new QueueException("$taskName timeout queue");
        $this->break_job = true;
        $data = $this->getDataFailed($payload['data'], $error);
        $queueManage->failedJob($this, $data, $error);
    }

    private function getDataFailed($queue, $exception)
    {
        $class = str_replace('Queue\\Jobs\\','', $queue['class']);
        $data = [
            'uid' => $queue['uid'],
            'payload' => $queue['payload'],
            'class' => $class,
            'error' => $exception->getMessage(),
            'failed' => $exception->getTraceAsString()
        ];
        return [
            'data' => json_encode($data),
            'queue' => 'failed_jobs',
            'exception' => $exception->getMessage() . ". Trace: " . $exception->getTraceAsString(),
            'created_at' => date('Y-m-d H:i:s')
        ];
    }

    public function pushFailedJob($data)
    {
        if (is_null($this->driver)) {
            $this->connect();
        }
        if (isset($data['failed'])) {
            unset($data['failed']);
        }
        $this->driver->from('failed_jobs')->insert($data);
    }

}