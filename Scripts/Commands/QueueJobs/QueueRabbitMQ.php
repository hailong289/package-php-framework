<?php
namespace Hola\Scripts\Commands\QueueJobs;
use Hola\Connection\RabbitMQ;
use Hola\Exceptions\QueueException;
use PhpAmqpLib\Channel\AMQPChannel;

class QueueRabbitMQ {
    private $queueName = 'jobs';
    private $queueConnection = 'rabbitmq';
    public $queueConnectionType = 'rabbitmq';
    private $break_job = false;
    private \PhpAmqpLib\Connection\AMQPStreamConnection|\PhpAmqpLib\Connection\AMQPSSLConnection|null $driver = null;
    private \PhpAmqpLib\Channel\AMQPChannel|null $channel = null;
    private $is_rollback = false;

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
        $this->driver = RabbitMQ::instance($this->queueConnection);
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
        return $this;
    }

    protected function initChannel(): void
    {
        $this->channel = $this->driver->channel();
        $this->channel->queue_declare(
            $this->queueName, false, true, false, false
        );
    }

    protected function makeCallback(QueueManage $qm): callable
    {
        return function (\AMQPMessage $msg) use ($qm) {
            $data = $this->data(json_decode($msg->body, true));
            $taskName = "{$data['class']}_".uid();

            if (!empty($data['timeout'])) {
                $qm->setTimeout((int)$data['timeout']);
            }

            $qm->execute($taskName, function() use ($msg, $qm, $data) {
                $qm->pendingJob();
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                try {
                    $class = $data['class'];
                    if (!method_exists($class, 'handle')) {
                        throw new QueueException("Handle missing in {$class}");
                    }
                    app()->callWithParams($class, $data['payload'])->handle();
                    $qm->doneJob();
                } catch (\Throwable $e) {
                    $qm->failedJob($this, $this->getDataFailed($data, $e), $e);
                }
            }, $data);
        };
    }

    protected function waitForJobs(QueueManage $qm, int $maxIdle, int $timeoutSec): void
    {
        $idle = 0;
        while (count($this->channel->callbacks) && !$this->break_job) {
            try {
                $this->channel->wait(null, false, $timeoutSec);
                $idle = 0;
            } catch (\AMQPTimeoutException $e) {
                if (++$idle >= $maxIdle) {
                    $qm->output->writeln(
                        "<info>No jobs after {$maxIdle} checks. Exiting...</info>"
                    );
                    break;
                }
            }
        }
    }

    protected function closeChannel(): void
    {
        $this->channel->close();
        $this->driver->close();
    }


    public function queueWork(QueueManage $queueManage)
    {
        $this->getQueue();
        $this->initChannel();
        $this->channel->basic_qos(null, 1, null);

        $this->channel->basic_consume(
            $this->queueName, '', false, false, false, false,
            $this->makeCallback($queueManage)
        );

        $this->waitForJobs($queueManage, 3, 3);
        $this->closeChannel();
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
            'error' => $exception->getMessage()
        ];
        return $data;
    }

    public function pushFailedJob($data)
    {
        if (isset($data['failed'])) {
            unset($data['failed']);
        }
        if (is_null($this->driver)) {
            $this->connect();
        }
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $channel = $this->driver->channel();
        $channel->queue_declare('failed_jobs', false, true, false, false);
        $attributes = [
            'delivery_mode' => \PhpAmqpLib\Message\AMQPMessage::DELIVERY_MODE_PERSISTENT,
            'content_type' => 'application/json',
        ];
        $msg = new \PhpAmqpLib\Message\AMQPMessage(
            $data,
            $attributes
        );
        $channel->basic_publish($msg, '', 'failed_jobs');
        $channel->close();
        $this->driver->close();
    }
}