<?php
namespace Scripts;

class QueueScript extends \System\Core\Command
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
//      passthru('php cli.php run:queue work'); use queue live
        $queue_name = $this->getOption('queue');
        $timeout_options = $this->getOption('timeout');
        $type = $this->getOption('type');
        $connection_arg = $this->getArgument('connection');

        if($connection_arg) $this->connection = $connection_arg;
        if($queue_name) $this->jobs_queue = $queue_name;
        if(!empty($timeout_options)) {
            $this->timeout = $timeout_options;
            ini_set('max_execution_time', $this->timeout);
        } else {
            ini_set('max_execution_time', $this->timeout); // timeout all job
        }
        $db = $this->getDB();
        if($db) {
            sleep(1);
            $queues = $this->getQueueList($db);

            foreach ($queues as $key=>$queue) {
                $key = $this->connection === 'database' ? $queue['id']:$key;
                $this->clearQueue($db, $queue, $key);
                if ($this->connection === 'database') {
                    $queue = json_decode($queue['data'], true);
                } else {
                    $queue = json_decode($queue, true);
                }

                $uid = $queue['uid'];
                $class = "Queue\\Jobs\\{$queue['class']}";
                $payload = $queue['payload'];
                $timeout = $queue['timeout'] ?? 0;
                if ($timeout > 0 && empty($timeout_options)) {
                    $this->timeout = $timeout;
                    ini_set('max_execution_time', $timeout); // timeout one job
                }
                $directory = __DIR__ROOT . "/$class.php";
                $this->output()->text("$class running ".PHP_EOL);
                try {
                    if (method_exists($class, 'handle')) {
                        $this->queueRunning = [
                            'key' => $key,
                            'queue' => $queue,
                            'payload' => $payload,
                            'uid' => $uid,
                            'class' => $class
                        ];
                        $this->startRunQueue($db, $queue, $key, $class, $payload, $uid);
                    } else {
                        $this->stopQueue($db, $payload, $class, $uid, new \Exception("Function handle in class $class does not exit"));
                        $this->output()->text("$class failed ".PHP_EOL);
                    }
                } catch (\Throwable $e) {
                    $this->stopQueue($db, $payload, $class, $uid, $e);
                    $this->output()->text("$class failed ".PHP_EOL);
                }
            }
            if(!empty($type) && $type === 'live') $this->handle();
        }
    }


    private function getDB()
    {
        $db = '';
        if($this->connection === 'database') {
            try {
                $db = new \System\Core\Database();
            }catch (\Throwable $e) {
                $this->output()->error([
                    "message" => $e->getMessage(),
                    "code" => $e->getCode(),
                    "line" => $e->getLine(),
                    "file" => $e->getFile(),
                    "trace" => $e->getTraceAsString()
                ]);
                return false;
            }
        } else {
            try {
                $db = \System\Core\Redis::work();
                if (!$db->isConnected()) {
                    $this->output()->error([
                        "message" => 'Redis connection is failed',
                        "code" => 503,
                    ]);
                    return false;
                }
            }catch (\Throwable $e) {
                $this->output()->error([
                    "message" => $e->getMessage(),
                    "code" => $e->getCode(),
                    "line" => $e->getLine(),
                    "file" => $e->getFile(),
                    "trace" => $e->getTraceAsString()
                ]);
                log_write($e);
                return false;
            }
        }
        return $db;
    }

    private function getQueueList($db)
    {
        try {
            if ($db instanceof \System\Core\Database) {
                $table = $this->jobs_queue === 'rollback_failed_job' ? 'failed_jobs' : 'jobs';
                $queue = $this->jobs_queue === 'rollback_failed_job' ? 'failed_jobs' : $this->jobs_queue;
                return $db->table($table)->where('queue', $queue)->get()->toArray();
            }
            if ($db instanceof \Redis) {
                $queue_name = $this->jobs_queue;
                if ($queue_name === 'rollback_failed_job') {
                    $queue_name = 'failed_jobs';
                }
                return $db->lrange("queue:{$queue_name}", 0, -1);
            }
        }catch (\Throwable $e) {
            $this->output()->error([
                "message" => $e->getMessage(),
                "code" => $e->getCode(),
                "line" => $e->getLine(),
                "file" => $e->getFile(),
                "trace" => $e->getTraceAsString()
            ]);
            log_write($e);
        }
        return null;
    }

    private function startRunQueue($db, $queue_first, $index, $class, $payload, $uid)
    {
        $start = new \DateTime();
        try {
            $work_class = new $class(...array_values($payload));
            $work_class->handle();
            $end = new \DateTime();
            $time = $end->diff($start)->format('%H:%I:%S');
            $this->output()->text("$class work success ---- Time: $time" . PHP_EOL);
        } catch (\Throwable $e) {
            $this->stopQueue($db, $payload, $class, $uid, $e);
            $end = new \DateTime();
            $time = $end->diff($start)->format('%H:%I:%S');
            $this->output()->text("$class work failed ----  Time: $time" . PHP_EOL);
        }
    }

    private function stopQueue($db, $payload, $class, $uid, $e)
    {
        try {
            if ($this->connection === 'database') {
                if ($db instanceof \System\Core\Database) {
                    $data = json_encode([
                        'uid' => $uid,
                        'payload' => $payload,
                        'class' => $class,
                        'error' => $e->getMessage()
                    ]);
                    $db::table('failed_jobs')->insert([
                        'data' => $data,
                        'queue' => 'failed_jobs',
                        'exception' => $e->getMessage() . ". Trace: " . base64_encode($e->getTraceAsString()),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            } else {
                if ($db instanceof \Redis) {
                    $data = json_encode([
                        'uid' => $uid,
                        'payload' => $payload,
                        'class' => $class,
                        'error' => $e->getMessage(),
                        'failed' => $e->getTraceAsString()
                    ]);
                    $db->rPush('queue:failed_jobs', $data);
                }
            }
        }catch (\Throwable $e) {
            $this->output()->error([
                "message" => $e->getMessage(),
                "code" => $e->getCode(),
                "line" => $e->getLine(),
                "file" => $e->getFile(),
                "trace" => $e->getTraceAsString()
            ]);
            log_write($e);
        }
    }

    private function clearQueue($db, $queue_first, $index) {
        try {
            if ($db instanceof \System\Core\Database) {
                $table = $this->jobs_queue === 'rollback_failed_job' ? 'failed_jobs':'jobs';
                $queue = $this->jobs_queue === 'rollback_failed_job' ? 'failed_jobs':$this->jobs_queue;
                $db::table($table)->where('queue', $queue)->where('id', $index)->delete();
            } else if ($db instanceof \Redis) {
                $queue_name = $this->jobs_queue;
                if ($queue_name === 'rollback_failed_job') {
                    $queue_name = 'failed_jobs';
                }
                $db->lRem("queue:{$queue_name}", $queue_first, $index);
            }
        }catch (\Throwable $e) {
            $this->output()->error([
                "message" => $e->getMessage(),
                "code" => $e->getCode(),
                "line" => $e->getLine(),
                "file" => $e->getFile(),
                "trace" => $e->getTraceAsString()
            ]);
            log_write($e);
        }
    }

    private function stopJobTimeout() {
        $error = error_get_last();
        if(!is_null($error)) {
            if (strpos($error['message'], "Maximum execution time of {$this->timeout} seconds exceeded") === false) {
                echo 'Other error: ' . print_r($error, true);
            } else {
                $db = $this->getDB();
                if (!empty($this->queueRunning) && $db) {
                    $key = $this->queueRunning['key'];
                    $queue = $this->queueRunning['queue'];
                    $payload = $this->queueRunning['payload'];
                    $class = $this->queueRunning['class'];
                    $uid = $this->queueRunning['uid'];
                    $this->stopQueue($db, $payload, $class, $uid, new \Exception("Timeout queue"));
                    $this->clearQueue($db, $queue, $key);
                    $this->output()->text("$class failed. Error: Timeout queue".PHP_EOL);
                    $queues = $this->getQueueList($db);
                    if($queues) { // Continue running despite the timeout error until the job ends
                        $command = 'php cli.php queue:run ';
                        if(!empty($this->getArgument('connection'))) $command .= "{$this->getArgument('connection')} ";
                        if(!empty($this->getOption('queue'))) $command .= "{$this->getOption('queue')} ";
                        passthru($command);
                    }
                }
            }
        }
    }
}
