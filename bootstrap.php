<?php
define('__DIR__ROOT', __DIR__);
require_once 'core/function.php';
// auto load file
$config_dir = array_diff(scandir('config'), array('..', '.'));
if (!empty($config_dir)) {
    foreach($config_dir as $item){
        if(file_exists(path_root('config/'.$item))){
            require_once path_root("config/".$item);
        }
    }
}
$trait_dir = array_diff(scandir('trait'), array('..', '.'));
if (!empty($trait_dir)) {
    foreach($trait_dir as $item){
        if(file_exists(path_root('trait/'.$item))){
            require_once path_root("trait/".$item);
        }
    }
}

$core_dir = array_diff(scandir('core'), array('..', '.'));
if (!empty($core_dir)) {
    foreach($core_dir as $item){
        if ($item == 'function.php') continue;
        if(file_exists(path_root('core/'.$item))){
            require_once path_root("core/".$item);
        }
    }
}
require_once 'router/web.php';
require_once 'app/App.php';
