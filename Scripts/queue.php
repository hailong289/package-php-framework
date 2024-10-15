<?php
namespace Hola\Scripts;

use Hola\Connection\RabbitMQ;
use Hola\Connection\Redis;
use Hola\Core\RedisCR;
use Hola\Database\DBO;

class QueueScript extends \Hola\Core\Command
{
    protected $command = 'queue:run';
    protected $command_description = 'Run a queue';
    protected $arguments = ['?connection'];
    protected $options = ['queue','?timeout','?type'];
    protected $jobs_queue = 'jobs';
    protected $connection = 'database';
    protected $timeout = 600; // default 10 minutes
    private $queueRunning = null;
    private $time_error = 0;
    private $break_job = false;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->connection = config_env('QUEUE_WORK', 'database');
        $queue_name = $this->getOption('queue');
        $type = $this->getOption('type');
        $connection_arg = $this->getArgument('connection');
        if(!empty($connection_arg)) $this->connection = $connection_arg;
        if(!empty($queue_name)) $this->jobs_queue = $queue_name;
        sleep(1);
        $this->switchDB($this->connection);
        if(!empty($type) && $type === 'live') $this->handle();
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
        ini_set('max_execution_time', $this->timeout);
    }

    private function data($data)
    {
        return [
            'key' => $data['key'] ?? 0,
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
                    $connection = DBO::connection($name, 'queue');
                    if (!$only_get) $this->queueWorkWithDB($connection);
                    break;
                case 'redis':
                    $connection = Redis::queueConnect($name);
                    if (!$only_get) $this->workQueueWithRedis($connection);
                    break;
                case 'rabbitmq':
                    $connection = RabbitMQ::queueConnect($name);
                    if (!$only_get) $this->workQueueRabbit($connection);
                    break;
                default:
                    $connection = DBO::connection('database', 'queue');
                    if (!$only_get) $this->queueWorkWithDB($connection);
                    break;
            }
        } catch (\Throwable $exception) {
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


    private function falied($conn, $data, $e)
    {
        try {
            $class = str_replace('Queue\\Jobs\\','', $data['class']);
            $data = [
                'uid' => $data['uid'],
                'payload' => $data['payload'],
                'class' => $class,
                'error' => $e->getMessage(),
                'failed' => $e->getTraceAsString()
            ];
            if ($conn instanceof \Hola\Database\DBO) {
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
                $data = json_encode($data);
                $connection = DBO::connection('database', 'queue');
                $connection->from('failed_jobs')->insert([
                    'data' => $data,
                    'queue' => 'failed_jobs',
                    'exception' => $e->getMessage() . ". Trace: " . base64_encode($e->getTraceAsString()),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        } catch (\Throwable $exception) {
            $this->output()->error([
                "message" => $exception->getMessage(),
                "code" => $exception->getCode(),
                "line" => $exception->getLine(),
                "file" => $exception->getFile(),
                "trace" => $exception->getTraceAsString()
            ]);
        }
    }

    private function queueWorkWithDB(\Hola\Database\DBO $db)
    {
        $queue_name = $this->jobs_queue;
        if ($queue_name === 'rollback_failed_job') {
            $queue_name = 'failed_jobs';
        }
        $list_queue = $db
            ->from($queue_name)
            ->where('queue', $queue_name)
            ->get()
            ->toArray();
        $list_queue = array_map(function ($item){
            $queue = json_decode($item['data'], true);
            $queue['key'] = $item['id'];
            return $queue;
        }, $list_queue ?? []);

        foreach ($list_queue as $queue) {
            $queue = $this->data($queue);
            if ($this->break_job) break;
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
                $end = new \DateTime();
                $time = $end->diff($start)->format('%H:%I:%S');
                $this->output()->text("{$queue['class']} work success ---- Time: $time");
            }catch (\Throwable $exception) {
                $this->output()->text("$class failed ");
                $this->falied($db, $queue, $exception);
            }
        }
    }

    private function workQueueWithRedis(\Redis $db)
    {
        $queue_name = $this->jobs_queue;
        if ($queue_name === 'rollback_failed_job') {
            $queue_name = 'failed_jobs';
        }
        $list_queue = $db->lrange("queue:{$queue_name}", 0, 5);
        foreach ($list_queue as $queue) {
            if ($this->break_job) break;
            $db->lPop("queue:{$queue_name}");
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
            } catch (\Throwable $exception) {
                $time = $this->endTimeJob($start);
                $this->output()->text("{$queue['class']} failed ---- Time: $time");
                $this->falied($db, $queue, $exception);
            }
        }

        $firstTask = $db->lindex("queue:{$queue_name}", 0);
        if (!empty($firstTask) && $this->break_job === false) {
            $this->workQueueWithRedis($db);
        }
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
            } catch (\Throwable $e) {
                $time = $this->endTimeJob($start);
                $this->output()->text("{$queue['class']} failed ---- Time: $time");
                $this->falied($db, $queue, $exception);
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
        if(!is_null($error)) {
            $seconds = $this->timeout > 1 ? 'seconds':'second';
            if (
                $error['type'] === E_ERROR &&
                strpos($error['message'], "Maximum execution time of {$this->timeout} $seconds exceeded") !== false &&
               !empty($this->queueRunning)
            ) {
                $this->break_job = true;
                $db = $this->switchDB($this->connection);
                if ($db) {
                    $this->falied($db, $this->queueRunning, new \Exception("Timeout queue"));
                    $this->output()->text("{$this->queueRunning['class']} failed. Error: Timeout queue".PHP_EOL);
                    $this->queueRunning = [];
                }
            }
        }
    }
}
