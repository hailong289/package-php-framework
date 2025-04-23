<?php
namespace Hola\Scripts\Commands\QueueJobs;
use Hola\Connection\RabbitMQ;
use Hola\Exceptions\QueueException;

class QueueRabbitMQ {
    private $queueName = 'jobs';
    private $queueConnection = 'rabbitmq';
    public $queueConnectionType = 'rabbitmq';
    private $break_job = false;
    private \PhpAmqpLib\Connection\AMQPStreamConnection|\PhpAmqpLib\Connection\AMQPSSLConnection|null $driver = null;
    private $channel = null;
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
        $this->channel = $this->driver->channel();
        $this->channel->queue_declare($this->queueName, false, true, false, false);
        return $this;
    }

    public function queueWork(QueueManage $queueManage)
    {
        $queueManage->eventTimeOut(fn ($payload) => $this->eventTimeOut($payload, $queueManage));
        $this->getQueue();
        $callback = function (\PhpAmqpLib\Message\AMQPMessage $msg) use ($queueManage) {
            $queue = json_decode($msg->body, true);
            $queue = $this->data($queue);
            $taskName = $queue['class'] . '_' . uid();
            if (!empty($queue['timeout'])) {
                $queueManage->setTimeOut((int)$queue['timeout']);
            }
            $callbackQueue = function () use ($queue, $msg, $queueManage) {
                $queueManage->pendingJob();
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                try {
                    if (!method_exists($queue['class'], 'handle')) {
                        throw new QueueException("function handle does not exits in {$queue['class']}");
                    }
                    app()->callWithParams($queue['class'], $queue['payload'])->handle();
                    $queueManage->doneJob();
                } catch (\Throwable $e) {
                    $data = $this->getDataFailed($queue, $exception);
                    $queueManage->failedJob($this, $data, $exception);
                }
            };
            $queueManage->execute($taskName, $callbackQueue, $queue);
        };

        $this->channel->basic_qos(null, 1, null);
        $consumer_tag = $this->channel->basic_consume($queue, '', false, false, false, false, $callback);
        $max_idle = 3;
        $idle_count = 0;
        while (count($this->channel->callbacks)) {
            if ($this->break_job) {
                break;
            }
            try {
                $this->channel->wait(null, false, 3);
                $idle_count = 0;
            } catch (\PhpAmqpLib\Exception\AMQPTimeoutException $e) {
                $idle_count++;
                if ($idle_count >= $max_idle) {
                    $queueManage->output->writeln("<info>No jobs after {$max_idle} attempts. Exiting...</info>");
                    break;
                }
            }
        }

        $this->channel->close();
        $this->driver->close();
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