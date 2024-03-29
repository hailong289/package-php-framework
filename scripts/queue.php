<?php
namespace Scripts;
class QueueScript extends \System\Core\Command
{
    protected $command = 'queue:run';
    protected $command_description = 'Run a queue';
    protected $arguments = [];
    protected $options = ['queue'];
    protected $jobs_queue = 'jobs';


    public function __construct()
    {
        parent::__construct();
    }

    public function handel()
    {
        ini_set('error_reporting', E_STRICT);
//        passthru('php cli.php run:queue work'); use queue live
        $queue = $this->getOption('queue');
        $db = $this->getDB();
        if($db) {
            $queue = $this->getQueueList($db);
            $uid = $queue['uid'];
            $class = QUEUE_WORK === 'database' ? str_replace('/', '\\', $queue['class']) : $queue['class'];
            $payload = $queue['payload'];
            $directory = __DIR__ROOT . "/$class.php";
            $key = QUEUE_WORK === 'database' ? $value['id']:$key;
            $this->output()->text("$class running ".PHP_EOL);
            try {
                if (method_exists($class, 'handle')) {
                    $this->startRunQueue($db, $value, $key, $class, $payload, $uid, $job_queue);
                } else {
                    $this->stopQueue($db, $payload, $class, $uid, new Exception("Function handle in class $class does not exit"));
                    removeQueue(QUEUE_WORK === 'database' ? $database : $redis, $value, $key, $job_queue);
                    $this->output()->text("$class failed ".PHP_EOL);
                }
            } catch (\Throwable $e) {
                removeQueue(QUEUE_WORK === 'database' ? $database : $redis, $value, $key, $job_queue);
                failedQueue(QUEUE_WORK === 'database' ? $database : $redis, $payload, $class, $uid, $e);
                $this->output()->text("$class failed ".PHP_EOL);
            }
        }
    }


    public function getDB()
    {
        $queue_connection = QUEUE_WORK;
        $db = '';
        if($queue_connection === 'database') {
            try {
                $db = new \System\Core\Database();
            }catch (\Throwable $e) {
                $this->output()->error([
                    "message" => $e->getMessage(),
                    "code" => $code,
                    "line" => $e->getLine(),
                    "file" => $e->getFile(),
                    "trace" => $e->getTraceAsString()
                ]);
                return false;
            }
        } else {
            try {
                $db = \System\Core\Redis::work();
                if (!$redis->isConnected()) {
                    $this->output()->error([
                        "message" => 'Redis connection is failed',
                        "code" => 503,
                    ]);
                    return false;
                }
            }catch (\Throwable $e) {
                $this->output()->error([
                    "message" => $e->getMessage(),
                    "code" => $code,
                    "line" => $e->getLine(),
                    "file" => $e->getFile(),
                    "trace" => $e->getTraceAsString()
                ]);
                return false;
            }
        }
        return $db;
    }

    public function getQueueList($db)
    {
        return QUEUE_WORK === 'database' ? $db->table($this->jobs_queue)->get()->toArray():$db->lrange("queue:{$this->jobs_queue}", 0, -1);
    }

    public function startRunQueue($db, $queue_first, $index, $class, $payload, $uid)
    {
        $start = new DateTime();
        try {
            $work_class = new $class(...array_values($payload));
            $work_class->handle();
            $end = new DateTime();
            $time = $end->diff($start)->format('%H:%I:%S');
            $this->output()->text("$class work success ---- Time: $time" . PHP_EOL);
        } catch (\Throwable $e) {
            $this->stopQueue($db, $payload, $class, $uid, $e);
            $end = new DateTime();
            $time = $end->diff($start)->format('%H:%I:%S');
            $this->output()->text("$class work failed ----  Time: $time" . PHP_EOL);
        }
        $this->clearQueue($db, $queue_first, $index);
    }

    public function stopQueue($db, $payload, $class, $uid, $e)
    {
        $data = json_encode([
            'uid' => $uid,
            'payload' => $payload,
            'class' => str_replace('\\','/',$class),
            'error' => $e->getMessage(),
            'failed' => $e->getTraceAsString()
        ]);
        if (QUEUE_WORK === 'database') {
            if ($db instanceof \System\Core\Database) {
                $db::table('failed_jobs')->insert([
                    'queue' => $data,
                    'created_at' => date(' Y-m-d H:i:s')
                ]);
            }
        } else {
            if ($db instanceof \Redis) {
                $db->rPush('queue:failed_jobs', $data);
            }
        }
    }

    public function clearQueue($db, $queue_first, $index) {
        if ($db instanceof \System\Core\Database) {
            $db::table($this->job_queue)->where('id', $index)->delete();
        } else if ($db instanceof \Redis) {
            $db->lRem("queue:{$this->job_queue}", $queue_first, $index);
        }
    }
}
