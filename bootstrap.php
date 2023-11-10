<?php
define('__DIR__ROOT', __DIR__);
require_once 'core/function.php';
// auto load file
$config_dir = glob('config/*.php');
if (!empty($config_dir)) {
    foreach($config_dir as $item){
        if(file_exists(path_root($item))){
            require_once path_root($item);
        }
    }
}
$trait_dir = glob('trait/*.php');
if (!empty($trait_dir)) {
    foreach($trait_dir as $item){
        if(file_exists(path_root($item))){
            require_once path_root($item);
        }
    }
}

$core_dir = glob('core/*.php');
if (!empty($core_dir)) {
    foreach($core_dir as $item){
        if ($item == 'core/function.php') continue;
        if(file_exists(path_root($item))){
            require_once path_root($item);
        }
    }
}

$queue_dir = glob('queue/*.php');
if (!empty($queue_dir)) {
    foreach($queue_dir as $item){
        if(file_exists(path_root($item))){
            require_once path_root($item);
        }
    }
}

$language = glob('language/*.php');
if (!empty($queue_dir)) {
    foreach($queue_dir as $item){
        if(file_exists(path_root($item))){
            $GLOBALS['data_lang'] = require(path_root($item));
        }
    }
}

require_once 'router/web.php';
require_once 'app/App.php';
