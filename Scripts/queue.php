<?php
namespace Hola\Scripts;

use Hola\Connection\RabbitMQ;
use Hola\Connection\Redis;
use Hola\Core\RedisCR;
use Hola\Database\DBO;
use Hola\Database\QueryBuilder;
use Hola\Queue\ListenQueue;

class QueueScript extends \Hola\Core\Command
{
    protected $command = 'queue:run';
    protected $command_description = 'Run a queue';
    protected $arguments = ['?type_connection'];
    protected $options = ['?queue','?timeout','?connection'];
    protected $jobs_queue = 'jobs';
    protected $connection = 'database';
    protected $connection_type = 'database';
    protected $timeout = 600; // default 10 minutes
    protected $custom_failed = false;
    private $queueRunning = null;
    private $break_job = false;
    private ListenQueue $listenQueue;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->listenQueue = ListenQueue::instance();
        $this->connection_type = config_env('QUEUE_WORK', 'database');
        $this->connection = config_env('QUEUE_CONNECTION', 'database');
        $queue_name = $this->getOption('queue');
        $connection = $this->getOption('connection');
        $connection_type = $this->getArgument('type_connection');
        if(!empty($connection_type)) $this->connection_type = $connection_type;
        if(!empty($queue_name)) $this->jobs_queue = $queue_name;
        if(!empty($connection)) $this->connection = $connection;
        sleep(1);
        $this->switchDB($this->connection_type);
    }

    public function setTimeOutJob($timeout = 0)
    {
        register_shutdown_function([$this, 'stopJobTimeout']);
        $timeout_options = $this->getOption('timeout');
        if(!empty($timeout_options)) {
            $this->timeout = $timeout_options;
        } else {
            $timeout = $timeout === 0 ? config('queue.timeout') : $timeout;
            $this->timeout = $timeout;
        }
        set_time_limit($this->timeout);
    }

    private function data($data)
    {
        return [
            'key' => $data['key'] ?? $data['id'] ?? 0,
            'uid' => $data['uid'],
            'class' => "Queue\\Jobs\\{$data['class']}",
            'payload' => $data['payload'],
            'timeout' => $data['timeout'] ?? 0,
        ];
    }

    private function switchDB($name, $only_get = false)
    {
        $connection = null;
        try {
            switch ($name) {
                case 'database':
                    $connection = DBO::connection($this->connection ?? $name, 'queue');
                    if (!$only_get) $this->queueWorkWithDB($connection);
                    break;
                case 'redis':
                    $connection = Redis::queueConnect($this->connection ?? $name);
                    if (!$only_get) $this->workQueueWithRedis($connection);
                    break;
                case 'rabbitmq':
                    $connection = RabbitMQ::instance($this->connection ?? $name);
                    if (!$only_get) $this->workQueueRabbit($connection);
                    break;
                default:
                    $connection = DBO::connection($this->connection ?? 'database', 'queue');
                    if (!$only_get) $this->queueWorkWithDB($connection);
                    break;
            }
        } catch (\Throwable $exception) {
            log_write($exception);
            $this->output()->error([
                "message" => $exception->getMessage(),
                "code" => $exception->getCode(),
                "line" => $exception->getLine(),
                "file" => $exception->getFile(),
                "trace" => $exception->getTraceAsString()
            ]);
            return false;
        }
        return $connection;
    }


    private function failed($data, $e)
    {
        try {
            $conn = $this->switchDB($this->connection_type, true);
            $class = str_replace('Queue\\Jobs\\','', $data['class']);
            $data = [
                'uid' => $data['uid'],
                'payload' => $data['payload'],
                'class' => $class,
                'error' => $e->getMessage(),
                'failed' => $e->getTraceAsString()
            ];
            if ($conn instanceof QueryBuilder) {
                unset($data['failed']);
                $data = json_encode($data);
                $conn->from('failed_jobs')->insert([
                    'data' => $data,
                    'queue' => 'failed_jobs',
                    'exception' => $e->getMessage() . ". Trace: " . base64_encode($e->getTraceAsString()),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            } elseif ($conn instanceof \Redis) {
                $data = json_encode($data);
                $conn->rPush('queue:failed_jobs', $data);
            } else if (
                $conn instanceof \PhpAmqpLib\Connection\AMQPStreamConnection ||
                $conn instanceof \PhpAmqpLib\Connection\AMQPSSLConnection
            ) {
                unset($data['failed']);
                $data = json_encode($data_queue, JSON_UNESCAPED_UNICODE);
                $channel = $conn->channel();
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
                // close connection
                $channel->close();
                $conn->close();
            }
        } catch (\Throwable $exception) {
            log_write($exception);
            $this->output()->error([
                "message" => $exception->getMessage(),
                "code" => $exception->getCode(),
                "line" => $exception->getLine(),
                "file" => $exception->getFile(),
                "trace" => $exception->getTraceAsString()
            ]);
        }
    }

    private function queueWorkWithDB(QueryBuilder $db)
    {
        $queue_name = $this->jobs_queue;
        if ($queue_name === 'rollback_failed_job') {
            $queue_name = 'failed_jobs';
        }

        while ($queue = $db->select([
                'id',
                'queue',
                'data'
            ])
            ->from($queue_name)
            ->where('queue', $queue_name)
            ->limit(1)
            ->first()) {
            if ($this->break_job) {
                break;
            }
            sleep(1);
            $queue = $queue->toArray();
            $queue['data'] = json_decode($queue['data'], true);
            $queue['data']['key'] = $queue['id'];
            $queue = $this->data($queue['data']);
            $db->from($queue_name)
                ->where('queue', $queue_name)
                ->where('id', $queue['key'])
                ->delete();
            $start = new \DateTime();
            $this->output()->text("{$queue['class']} running");
            try {
                if (!method_exists($queue['class'], 'handle')) {
                    throw new \Exception("function handle does not exits in {$queue['class']}");
                }
                $this->setTimeOutJob($queue['timeout']);
                $this->queueRunning = $queue;
                app()->callWithParams($queue['class'], $queue['payload'])->handle();
                $time = $this->endTimeJob($start);
                $this->output()->text("{$queue['class']} work success ---- Time: $time");
                try {
                    if ($this->listenQueue->isDone()) {
                        $this->listenQueue->trigger('success', $queue);
                    }
                } catch (\Throwable $exception) {
                    log_write($exception);
                }
            }catch (\Throwable $exception) {
                $time = $this->endTimeJob($start);
                $this->output()->text("$class failed  ---- Time: $time");
                if ($this->listenQueue->isFailed()) {
                    $this->listenQueue->trigger('failed', $queue, $exception);
                    if (isset($this->listenQueue->bindings['connection'])) {
                        $this->connection = $this->listenQueue->bindings['connection'];
                    }
                    if (isset($this->listenQueue->bindings['connection_type'])) {
                        $this->connection_type = $this->listenQueue->bindings['connection_type'];
                    }
                }
                if (!$this->custom_failed) {
                    $this->failed($queue, $exception);
                }
            }
        }
        return;
    }

    private function workQueueWithRedis(\Redis $db)
    {
        $queue_name = $this->jobs_queue;
        if ($queue_name === 'rollback_failed_job') {
            $queue_name = 'failed_jobs';
        }
        while ($queue = $db->lPop("queue:{$queue_name}")) {
            sleep(1);
            if ($this->break_job) {
                break;
            }
            $queue = $this->data(json_decode($queue, true));
            $start = new \DateTime();
            $this->output()->text("{$queue['class']} running");
            try {
                if (!method_exists($queue['class'], 'handle')) {
                    throw new \Exception("function handle does not exits in {$queue['class']}");
                }
                $this->setTimeOutJob($queue['timeout']);
                $this->queueRunning = $queue;
                app()->callWithParams($queue['class'], $queue['payload'])->handle();
                $time = $this->endTimeJob($start);
                $this->output()->text("{$queue['class']} work success ---- Time: $time");
                try {
                    if ($this->listenQueue->isDone()) {
                        $this->listenQueue->trigger('success', $queue);
                    }
                } catch (\Throwable $exception) {
                    log_write($exception);
                }
            } catch (\Throwable $exception) {
                $time = $this->endTimeJob($start);
                $this->output()->text("{$queue['class']} failed ---- Time: $time");
                if ($this->listenQueue->isFailed()) {
                    $this->listenQueue->trigger('failed', $queue, $exception);
                    if (isset($this->listenQueue->bindings['connection'])) {
                        $this->connection = $this->listenQueue->bindings['connection'];
                    }
                    if (isset($this->listenQueue->bindings['connection_type'])) {
                        $this->connection_type = $this->listenQueue->bindings['connection_type'];
                    }
                }
                if (!$this->custom_failed) {
                    $this->failed($queue, $exception);
                }
            }
        }
        return;
    }

    private function endTimeJob($start)
    {
        $end = new \DateTime();
        $time = $end->diff($start)->format('%H:%I:%S');
        return $time;
    }

    public function workQueueRabbit(
        \PhpAmqpLib\Connection\AMQPStreamConnection|
        \PhpAmqpLib\Connection\AMQPSSLConnection $db
    )
    {
        ini_set('error_reporting', E_STRICT);
        $queue = $this->jobs_queue;
        $channel = $db->channel();
        $channel->queue_declare($queue, false, true, false, false);

        $callback = function (\PhpAmqpLib\Message\AMQPMessage $msg) use ($db, $channel) {
            $queue = json_decode($msg->body, true);
            $queue = $this->data($queue);
            $start = new \DateTime();
            $this->output()->text("{$queue['class']} running ");
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            try {
                if (!method_exists($queue['class'], 'handle')) {
                    throw new \Exception("function handle does not exits in {$queue['class']}");
                }
//                $this->setTimeOutJob($queue['timeout']);
                $this->queueRunning = $queue;
                app()->callWithParams($queue['class'], $queue['payload'])->handle();
                $time = $this->endTimeJob($start);
                $this->output()->text("{$queue['class']} work success ---- Time: $time");
                try {
                    if ($this->listenQueue->isDone()) {
                        $this->listenQueue->trigger('success', $queue);
                    }
                } catch (\Throwable $exception) {
                    log_write($exception);
                }
            } catch (\Throwable $e) {
                $time = $this->endTimeJob($start);
                $this->output()->text("{$queue['class']} failed ---- Time: $time");
                if ($this->listenQueue->isFailed()) {
                    $this->listenQueue->trigger('failed', $queue, $exception);
                    if (isset($this->listenQueue->bindings['connection'])) {
                        $this->connection = $this->listenQueue->bindings['connection'];
                    }
                    if (isset($this->listenQueue->bindings['connection_type'])) {
                        $this->connection_type = $this->listenQueue->bindings['connection_type'];
                    }
                }
                if (!$this->custom_failed) {
                    $this->failed($queue, $exception);
                }
            }
        };

        $channel->basic_qos(null, 1, null);
        $consumer_tag = $channel->basic_consume($queue, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $db->close();
    }

    private function stopJobTimeout() {
        $error = error_get_last();
        if (!is_null($error)) {
            $seconds = $this->timeout > 1 ? 'seconds' : 'second';
            if (
                $error['type'] === E_ERROR &&
                strpos($error['message'], "Maximum execution time of {$this->timeout} $seconds exceeded") !== false &&
                !empty($this->queueRunning)
            ) {
                $exception = new \Exception("Timeout queue");
                $this->break_job = true;
                if ($this->listenQueue->isFailed()) {
                    $this->listenQueue->trigger('failed', $this->queueRunning, $exception);
                    if (isset($this->listenQueue->bindings['connection'])) {
                        $this->connection = $this->listenQueue->bindings['connection'];
                    }
                    if (isset($this->listenQueue->bindings['connection_type'])) {
                        $this->connection_type = $this->listenQueue->bindings['connection_type'];
                    }
                }
                if (!$this->custom_failed) {
                    $this->failed($this->queueRunning, $exception);
                }
                $this->output()->error("{$this->queueRunning['class']} failed. Error: Timeout queue" . PHP_EOL);
                $this->queueRunning = [];
            }
        }
    }
}
