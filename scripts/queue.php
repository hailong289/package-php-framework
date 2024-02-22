<?php
ini_set('error_reporting', E_STRICT);
require_once '../core/function.php';
require_once('../config/constant.php');
require_once('../config/database.php');
require_once '../trait/QueryBuilder.php';
require_once '../core/Connection.php';
require_once '../core/Redis.php';
require_once '../trait/QueryBuilder.php';

$options = getopt("", ['queue::']);
if(count($argv)) unset($argv[0]);
$argv = array_values($argv);
if(isset($options['queue'])) {
    $redis = \App\Core\Redis::work();
    $first_live = false;
    if($options['queue'] === 'work') {
        $queue_list = $redis->lrange('queue:job',0,-1);
        $queue_list = array_reverse($queue_list);
        foreach ($queue_list as $key=>$value) {
            $queue = json_decode($value, true);
            $class = $queue['class'];
            $payload = $queue['payload'];
            if(file_exists('../'.$class.'.php')){
                require_once '../'.$class.'.php';
                if (method_exists($class,'handle')) {
                    runQueue($redis, $value, $key, $class, $payload);
                } else {
                    throw new Exception('class handle does not exit');
                }
            } else {
                throw new Exception("File $class not exit");
            }
        }
    } else if ($options['queue'] === 'live') {
        while (true) {
            if($first_live) sleep(5);
            if(!$first_live) $first_live = true;
            passthru('php CreateQueue.php --queue=work_live');
        }
    } else if ($options['queue'] === 'work_live') {
       $queue_list = $redis->lrange('queue:job',0,-1);
       $index = count($queue_list) - 1;
       $queue_first = $queue_list[$index];
       if(count($queue_list)) {
           $queue = json_decode($queue_first, true);
           $class = $queue['class'];
           $payload = $queue['payload'];
           if(file_exists('../'.$class.'.php')){
               require_once '../'.$class.'.php';
               if (method_exists($class,'handle')) {
                   runQueue($redis, $queue_first, $index, $class, $payload);
               } else {
                   throw new Exception('class handle does not exit');
               }
           } else {
               throw new Exception("File $class not exit");
           }
       }
    }
}

function runQueue($redis, $queue_first, $index, $class, $payload)
{
    try {
        $work_class = new $class(...array_values($payload));
        $work_class->handle();
        echo "$class work success \n";
    }catch (\Throwable $e) {
        $redis->rPush('queue:job_failed', json_encode([
            'payload' => $payload,
            'class' => $class,
            'error' => $e->getMessage(),
            'failed' => $e->getTraceAsString()
        ]));
        echo "$class work failed \n";
    }
    $redis->lRem('queue:job',$queue_first, $index);
}