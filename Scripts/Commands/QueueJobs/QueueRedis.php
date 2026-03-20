<?php

namespace Hola\Scripts\Commands\QueueJobs;

use Hola\Connection\ConnectionManager;
use Hola\Connection\Redis;
use Hola\Exceptions\QueueException;

class QueueRedis {
    private $queueName = 'jobs';
    private $queueConnection = 'redis';
    public $queueConnectionType = 'redis';
    private $break_job = false;
    private \Redis|null $driver = null;
    private $is_rollback = false;

    private function data($data)
    {
        return [
            'key' => $data['key'] ?? $data['id'] ?? 0,
            'uid' => $data['uid'],
            'class' => stripslashes($data['class']),
            'payload' => $data['payload'],
            'timeout' => $data['timeout'] ?? 0,
            'tries' => $data['tries'] ?? 0,
            'delayTries' => $data['delayTries'] ?? 0,
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
        $this->driver = app()
            ->get(ConnectionManager::class)
            ->setConfigName('queue')
            ->setConnectionType('redis')
            ->setConnectionName($this->queueConnection)
            ->getConnection();
        return $this;
    }

    public function getQueue()
    {
        if (is_null($this->driver)) {
            $this->connect();
        }

        if ($this->is_rollback) {
            $this->queueName = 'failed_jobs';
        }

        $data = $this->driver->lPop("queue:{$this->queueName}");
        if (empty($data)) {
            return null;
        }

        $queueData = $this->data(json_decode($data, true));
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
            'failed' => $exception->getTraceAsString(),
            'tries' => $queue['tries'] ?? 0,
            'delayTries' => $queue['delayTries'] ?? 0,
        ];
        return $data;
    }

    public function pushFailedJob($data)
    {
        if (is_null($this->driver)) {
            $this->connect();
        }
        $this->driver->rPush('queue:failed_jobs', json_encode($data));
    }

    public function retryFailedJob(QueueManage $queueManage, $queue)
    {
        $queueManage->eventTimeOut(fn ($payload) => $this->eventTimeOut($payload, $queueManage));
        sleep(1);
        $taskName = $queue['uid'] ?? uid();

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