<?php

namespace Hola\Scripts\Commands\QueueJobs;
use Hola\Queue\ListenQueue;

class QueueManage {
    private $timeout;
    private $payload;
    private $triggers = [];
    private $startTime;
    private $endTime;
    private $tries = 0;
    private $delayTries = 0;
    private $numberTries = 0;
    public $output;

    private function registerSignalHandler()
    {
        pcntl_signal(SIGALRM, function () {
            if (isset($this->triggers['callback'])) {
                $this->triggers['callback']($this->payload);
            } else {
                echo "Task {$this->taskName} timeout\n";
            }
            exit;
        });
    }

    public function listen()
    {
        return ListenQueue::instance();
    }

    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;
    }
    
    public function setTries(int $retry)
    {
        $this->tries = $retry;
    }
    
    public function setDelayTries(int $delay) {
        $this->delayTries = $delay;
    }

    public function eventTimeOut($callback) {
        $this->triggers['callback'] = $callback;
        return $this;
    }

    public function startTimeJob() {
        $this->startTime = new \DateTime();
        return $this;
    }

    public function endTimeJob() {
        $end = new \DateTime();
        $time = $end->diff($this->startTime)->format('%H:%I:%S');
        $this->endTime = $time;
    }

    public function setOutput($output)
    {
        $this->output = $output;
        return $this;
    }

    public function execute(string $taskName, callable $taskCallback, $data = [])
    {
        if (!extension_loaded('pcntl')) {
            throw new \Exception('PCNTL extension is NOT enabled.');
        }
        $this->payload = ['taskName' => $taskName, 'data' => $data];
        $this->registerSignalHandler();
        pcntl_alarm($this->timeout);
        $taskCallback();
        pcntl_signal_dispatch();
    }


    public function pendingJob()
    {
        $this->startTimeJob();
        $this->output->writeln("<info>{$this->payload['taskName']} running</info>");
    }

    public function doneJob()
    {
        $this->endTimeJob();
        $this->output->writeln("<info>{$this->payload['taskName']} success. ===== Time: {$this->endTime}</info>");
        $this->listen()->trigger('success', $this->payload['data']);
        pcntl_alarm(0);
    }

    public function failedJob(
        QueueDatabase|
        QueueRedis|
        QueueRabbitMQ $driver,
        $data,
        \Throwable $exception
    ) {
        $this->endTimeJob();
        $this->output->writeln("<error>{$this->payload['taskName']} failed. ===== Time: {$this->endTime}</error>");
        $this->listen()->trigger('failed', $this->payload['data']);
        if (
            $this->listen()->isBindingConnection() &&
            $this->listen()->isBindingConnectionType()
        ) {
            $this->connection = $this->listenQueue->bindings['connection'];
            $this->connection_type = $this->listenQueue->bindings['connection_type'];
            // TODO: switch to other connection
            if ($driver->queueConnectionType != $this->connection_type) {
                $switch = (new SwitchDB($this->connection, $this->connection_type))->handle();
                $driver = $switch->getDriver();
            }
        }
        $this->output->writeln("<error>{$exception->getMessage()}</error>");
        logs()->write_error($exception);
        $driver->pushFailedJob($data);
        pcntl_alarm(0);

        $jobData = $this->payload['data'] ?? [];
        $this->tries = (int)($jobData['tries'] ?? 0);
        $this->delayTries = (int)($jobData['delayTries'] ?? 0);

        if ($this->tries > 0 && method_exists($driver, 'retryFailedJob')) {
            sleep($this->delayTries);
            $this->tries--;
            $jobData['tries'] = $this->tries;
            $driver->retryFailedJob($this, $jobData);
        }
    }

}