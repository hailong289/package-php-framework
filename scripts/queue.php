<?php
ini_set('error_reporting', E_STRICT);
$redis = \System\Core\Redis::work();
if(!$redis->isConnected()) {
    die('Redis connection is failed');
}
if ($type_queue === 'work') {
    $queue_list = $redis->lrange("queue:$job_queue", 0, -1);
    foreach ($queue_list as $key => $value) {
        $queue = json_decode($value, true);
        $uid = $queue['uid'];
        $class = $queue['class'];
        $payload = $queue['payload'];
        $directory = __DIR__ROOT ."/$class.php";
        echo "$class running \n";
        try {
            if (method_exists($class, 'handle')) {
                runQueue($redis, $value, $key, $class, $payload, $uid, $job_queue);
            } else {
                failedQueue($redis, $payload, $class, $uid, new Exception('class handle does not exit'));
                removeQueue($redis, $value, $key, $job_queue);
                echo "$class failed \n";
            }
        }catch (\Throwable $e) {
            removeQueue($redis, $value, $key, $job_queue);
            failedQueue($redis, $payload, $class, $uid, $e);
            echo "$class failed \n";
        }
    }

} else if ($type_queue === 'live') {
    $queue_list = $redis->lrange("queue:$job_queue", 0, -1);
    foreach ($queue_list as $key => $value) {
        $queue = json_decode($value, true);
        $uid = $queue['uid'];
        $class = $queue['class'];
        $payload = $queue['payload'];
        $directory = __DIR__ROOT ."/$class.php";
        echo "$class running \n";
        try {
            if (method_exists($class, 'handle')) {
                runQueue($redis, $value, $key, $class, $payload, $uid, $job_queue);
            } else {
                removeQueue($redis, $value, $key, $job_queue);
                failedQueue($redis, $payload, $class, $uid, new Exception('class handle does not exit'));
                echo "$class failed \n";
            }
        }catch (\Throwable $e) {
            removeQueue($redis, $value, $key, $job_queue);
            failedQueue($redis, $payload, $class, $uid, $e);
            echo "$class failed \n";
        }
    }
    while(true) {
        sleep(5);
        passthru('php cli.php run:queue work');
    }
}


function runQueue($redis, $queue_first, $index, $class, $payload, $uid, $job_queue)
{
    $start = new DateTime();
    try {
        $work_class = new $class(...array_values($payload));
        $work_class->handle();
        $end = new DateTime();
        $time = $end->diff($start)->format('%H:%I:%S');
        echo "$class work success ---- Time: $time".PHP_EOL;
    }catch (\Throwable $e) {
        failedQueue($redis, $payload, $class, $uid, $e);
        $end = new DateTime();
        $time = $end->diff($start)->format('%H:%I:%S');
        echo "$class work failed ----  Time: $time".PHP_EOL;
    }
    removeQueue($redis, $queue_first, $index, $job_queue);
}

function removeQueue($redis, $queue_first, $index, $job_queue)
{
    $redis->lRem("queue:$job_queue",$queue_first, $index);
}

function failedQueue($redis, $payload, $class, $uid, $e)
{
    $redis->rPush('queue:job_failed', json_encode([
        'uid' => $uid,
        'payload' => $payload,
        'class' => $class,
        'error' => $e->getMessage(),
        'failed' => $e->getTraceAsString()
    ]));
}
