<?php
ini_set('error_reporting', E_STRICT);

if (QUEUE_WORK === 'database') {
    try {
        $database = new \System\Core\Database();
    }catch (\Throwable $e) {
        $code = (int)$e->getCode();
        echo json_encode([
            "message" => $e->getMessage(),
            "code" => $code,
            "line" => $e->getLine(),
            "file" => $e->getFile(),
            "trace" => $e->getTraceAsString()
        ]);
        exit();
    }

} else {
    try {
        $redis = \System\Core\Redis::work();
        if (!$redis->isConnected()) {
            echo json_encode([
                "message" => 'Redis connection is failed',
                "code" => 503,
            ]);
            exit();
        }
    }catch (\Throwable $e) {
        $code = (int)$e->getCode();
        echo json_encode([
            "message" => $e->getMessage(),
            "code" => $code,
            "line" => $e->getLine(),
            "file" => $e->getFile(),
            "trace" => $e->getTraceAsString()
        ]);
        exit();
    }

}

if ($type_queue === 'work') {
    if (QUEUE_WORK === 'database') {
        $queue_list = $database->table('jobs')->get()->toArray();
    } else {
        $queue_list = $redis->lrange("queue:$job_queue", 0, -1);
    }
    foreach ($queue_list as $key => $value) {
        if (QUEUE_WORK === 'database') {
            $queue = json_decode($value['queue'], true);
        } else {
            $queue = json_decode($value, true);
        }
        $uid = $queue['uid'];
        $class = QUEUE_WORK === 'database' ? str_replace('/', '\\', $queue['class']) : $queue['class'];
        $payload = $queue['payload'];
        $directory = __DIR__ROOT . "/$class.php";
        $key = QUEUE_WORK === 'database' ? $value['id']:$key;
        echo "$class running \n";
        try {
            if (method_exists($class, 'handle')) {
                runQueue(QUEUE_WORK === 'database' ? $database : $redis, $value, $key, $class, $payload, $uid, $job_queue);
            } else {
                failedQueue(QUEUE_WORK === 'database' ? $database : $redis, $payload, $class, $uid, new Exception("Function handle in class $class does not exit"));
                removeQueue(QUEUE_WORK === 'database' ? $database : $redis, $value, $key, $job_queue);
                echo "$class failed \n";
            }
        } catch (\Throwable $e) {
            log_debug($e);
            removeQueue(QUEUE_WORK === 'database' ? $database : $redis, $value, $key, $job_queue);
            failedQueue(QUEUE_WORK === 'database' ? $database : $redis, $payload, $class, $uid, $e);
            echo "$class failed \n";
        }
    }

} else if ($type_queue === 'live') {
    if (QUEUE_WORK === 'database') {
        $queue_list = $database->table('jobs')->get()->values();
    } else {
        $queue_list = $redis->lrange("queue:$job_queue", 0, -1);
    }
    foreach ($queue_list as $key => $value) {
        if (QUEUE_WORK === 'database') {
            $queue = json_decode($value['queue'], true);
        } else {
            $queue = json_decode($value, true);
        }
        $uid = $queue['uid'];
        $class = QUEUE_WORK === 'database' ? str_replace('/', '\\', $queue['class']) : $queue['class'];
        $payload = $queue['payload'];
        $directory = __DIR__ROOT . "/$class.php";
        $key = QUEUE_WORK === 'database' ? $value['id']:$key;
        echo "$class running \n";
        try {
            if (method_exists($class, 'handle')) {
                runQueue(QUEUE_WORK === 'database' ? $database : $redis, $value, $key, $class, $payload, $uid, $job_queue);
            } else {
                failedQueue(QUEUE_WORK === 'database' ? $database : $redis, $payload, $class, $uid, new Exception('class handle does not exit'));
                removeQueue(QUEUE_WORK === 'database' ? $database : $redis, $value, $key, $job_queue);
                echo "$class failed \n";
            }
        } catch (\Throwable $e) {
            log_debug($e);
            removeQueue(QUEUE_WORK === 'database' ? $database : $redis, $value, $key, $job_queue);
            failedQueue(QUEUE_WORK === 'database' ? $database : $redis, $payload, $class, $uid, $e);
            echo "$class failed \n";
        }
    }
    while (true) {
        sleep(5);
        passthru('php cli.php run:queue work');
    }
}


function runQueue($db, $queue_first, $index, $class, $payload, $uid, $job_queue)
{
    $start = new DateTime();
    try {
        $work_class = new $class(...array_values($payload));
        $work_class->handle();
        $end = new DateTime();
        $time = $end->diff($start)->format('%H:%I:%S');
        echo "$class work success ---- Time: $time" . PHP_EOL;
    } catch (\Throwable $e) {
        failedQueue($db, $payload, $class, $uid, $e);
        $end = new DateTime();
        $time = $end->diff($start)->format('%H:%I:%S');
        echo "$class work failed ----  Time: $time" . PHP_EOL;
    }
    removeQueue($db, $queue_first, $index, $job_queue);
}

function removeQueue($db, $queue_first, $index, $job_queue)
{
    if (QUEUE_WORK === 'database') {
        if ($db instanceof \System\Core\Database) {
            $db::table($job_queue)->where('id', $index)->delete();
        }
    } else {
        if ($db instanceof \Redis) {
            $db->lRem("queue:$job_queue", $queue_first, $index);
        }
    }
}

function failedQueue($db, $payload, $class, $uid, $e)
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
