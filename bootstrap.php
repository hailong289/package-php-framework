<?php
define('__DIR__ROOT', __DIR__);
// tự động load config
$config_dir = scandir('config'); // hàm lấy các file trong folder configs
if(!empty($config_dir)){
    foreach($config_dir as $item){
        if($item !='.' && $item !='..' && file_exists('config/'.$item)){
            require_once "config/".$item;
        }
    }
}
require_once "core/SessionOrCookie.php";
require_once 'core/router.php';
require_once 'core/BaseController.php';
require_once 'core/Request.php';
require_once 'router/web.php';
require_once 'core/Connection.php'; // connection database
require_once 'trait/QueryBuilder.php'; // xử lý query builder
require_once 'core/Database.php';
require_once 'core/Model.php';


require_once 'app/App.php';
