<?php
$options = getopt("", ['queue::']);
if(count($argv)) unset($argv[0]);
$argv = array_values($argv);
if($argv[0] === 'work') {
    $redis = \App\Core\Redis::work();
    $queue_list = $redis->get('queue:job');
    var_dump($queue_list);
}