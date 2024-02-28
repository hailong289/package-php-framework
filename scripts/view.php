<?php
if(count($argv)) unset($argv[0]);
$argv = array_values($argv);
if(!empty($argv[0])) {
    $command = explode(':',$argv[0]);
    if(empty($command[1])) {
        echo "Name view not null";
    }
    if($command[0] === 'create') {
        $folder = explode('/',$command[1]);
        if(count($folder) > 1){
            unset($folder[count($folder)-1]);
            $folder = '/'.implode('/',$folder);
        } else $folder = $command[1];
        if (!file_exists(dirname(__DIR__, 1) .'/App/Views'.$folder)) {
            if (!mkdir($concurrentDirectory = dirname(__DIR__, 1) . '/views'.$folder, 0777, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }
        if(!file_exists(dirname(__DIR__, 1) ."/App/Views/$command[1].view.php")) {
            file_put_contents(dirname(__DIR__, 1) ."/App/Views/$command[1].view.php",'', FILE_APPEND);
        } else {
            echo "View $command[1] already exist";
        }
    }
}