<?php
namespace Hola\Scripts;

use Hola\Core\Connection;

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

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        register_shutdown_function([$this, 'stopJobTimeout']);
        $this->timeout = config_env('QUEUE_TIMEOUT', 600);
        $this->connection = config_env('QUEUE_WORK', 'database');
        $queue_name = $this->getOption('queue');
        $timeout_options = $this->getOption('timeout');
        $type = $this->getOption('type');
        $connection_arg = $this->getArgument('connection');
        if(!empty($connection_arg)) $this->connection = $connection_arg;
        if(!empty($queue_name)) $this->jobs_queue = $queue_name;
        if(!empty($timeout_options)) {
            $this->timeout = $timeout_options;
            ini_set('max_execution_time', $this->timeout);
        } else {
            ini_set('max_execution_time', $this->timeout); // timeout all job
        }
        sleep(1);
        $this->switchDB($this->connection);
        if(!empty($type) && $type === 'live') $this->handle();
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
                    $connection = new \Hola\Core\Database();
                    if (!$only_get) $this->queueWorkWithDB($connection);
                    break;
                case 'redis':
                    $connection = \Hola\Core\Redis::work();
                    if (!$only_get) $this->workQueueWithRedis($connection);
                    break;
                case 'rabbitMQ':
                    $connection = Connection::instanceRabbitMQ();
                    if (!$only_get) $this->workQueueRabbit($connection);
                    break;
                default:
                    $connection = new \Hola\Core\Database();
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
            if ($conn instanceof \Hola\Core\Database) {
                unset($data['failed']);
                $data = json_encode($data);
                $conn->table('failed_jobs')->insert([
                    'data' => $data,
                    'queue' => 'failed_jobs',
                    'exception' => $e->getMessage() . ". Trace: " . base64_encode($e->getTraceAsString()),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            } elseif ($conn instanceof \Hola\Core\Redis) {
                $data = json_encode($data);
                $conn->rPush('queue:failed_jobs', $data);
            } else if (
                $conn instanceof \PhpAmqpLib\Connection\AMQPStreamConnection ||
                $conn instanceof \PhpAmqpLib\Connection\AMQPSSLConnection
            ) {
                unset($data['failed']);
                $data = json_encode($data);
                $connection = new \Hola\Core\Database();
                $connection->table('failed_jobs')->insert([
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

    private function queueWorkWithDB(\Hola\Core\Database $db)
    {

        $queue_name = $this->jobs_queue;
        if ($queue_name === 'rollback_failed_job') {
            $queue_name = 'failed_jobs';
        }
        $db = $db::instance(); // set table
        $list_queue = $db
            ->table($queue_name)
            ->where('queue', $queue_name)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->toArray();
        $list_queue = array_map(function ($item){
            $queue = json_decode($item['data'], true);
            $queue['key'] = $item['id'];
            return $queue;
        }, $list_queue ?? []);

        foreach ($list_queue as $queue) {
            $queue = $this->data($queue);
            $db->table($queue_name)
                ->where('queue', $queue_name)
                ->where('id', $queue['key'])
                ->delete();
            $start = new \DateTime();
            $this->output()->text("{$queue['class']} running");
            try {
                if (!method_exists($queue['class'], 'handle')) {
                    throw new \Exception("function handle does not exits in {$queue['class']}");
                }
                $this->queueRunning = $queue;
                $work_class = new $queue['class'](...array_values($queue['payload']));
                $work_class->handle();
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
        $list_queue = $db->lrange("queue:{$queue_name}", 0, -1);
        foreach ($list_queue as $queue) {
            $db->rPop("queue:{$queue_name}");
            $queue = $this->data(json_decode($queue, true));
            $start = new \DateTime();
            $this->output()->text("{$queue['class']} running");
            try {
                if (!method_exists($queue['class'], 'handle')) {
                    throw new \Exception("function handle does not exits in {$queue['class']}");
                }
                $this->queueRunning = $queue;
                $work_class = new $queue['class'](...array_values($queue['payload']));
                $work_class->handle();
                $time = $this->endTimeJob($start);
                $this->output()->text("{$queue['class']} work success ---- Time: $time");
            } catch (\Throwable $exception) {
                $time = $this->endTimeJob($start);
                $this->output()->text("{$queue['class']} failed ---- Time: $time");
                $this->falied($db, $queue, $exception);
            }
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

        $callback = function (\PhpAmqpLib\Message\AMQPMessage $msg) use ($db) {
            $queue = json_decode($msg->body, true);
            $queue = $this->data($queue);
            $this->output()->text("{$queue['class']} running ");
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            try {
                if (!method_exists($queue['class'], 'handle')) {
                    throw new \Exception("function handle does not exits in {$queue['class']}");
                }
                $this->queueRunning = $queue;
                $work_class = new $queue['class'](...array_values($queue['payload']));
                $work_class->handle();
                $time = $this->endTimeJob($start);
                $this->output()->text("{$queue['class']} work success ---- Time: $time");
            } catch (\Throwable $e) {
                $time = $this->endTimeJob($start);
                $this->output()->text("{$queue['class']} failed ---- Time: $time");
                $this->falied($db, $queue, $exception);
            }
        };

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue, '', false, false, false, false, $callback);

        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $db->close();
    }

    private function stopJobTimeout() {
        $error = error_get_last();
        if(!is_null($error)) {
            if (
                $error['type'] === E_ERROR &&
                strpos($error['message'], "Maximum execution time of") !== false &&
               !empty($this->queueRunning)
            ){
                $db = $this->switchDB($this->connection);
                if ($db) {
                    $this->falied($db, $this->queueRunning, new \Exception("Timeout queue"));
                    $this->output()->text("$class failed. Error: Timeout queue".PHP_EOL);
                }
            }
        }
    }
}
