<?php
namespace Hola\Core;
class Redis {
    private static $redis;

    public function __construct()
    {
        self::$redis = Connection::getInstanceRedis(config_env('DB_ENVIRONMENT','default'), config_env('REDIS_CONNECTION','redis'));
    }

    public static function work() {
        self::$redis = Connection::getInstanceRedis(config_env('DB_ENVIRONMENT','default'), config_env('REDIS_CONNECTION','redis'));
        return self::$redis;
    }

    public static function isConnect() {
        if(!self::$redis) echo 'Redis connect failed';
        echo 'Redis connect successfully';
    }

    public static function cache($tags, $data, $time = 3600)
    {
        self::$redis->set($tags, json_encode($data));
        if($time > 0) self::$redis->expire($tags, $time);
        return $data;
    }

    public static function cacheRPush($tags, $data, $time = 3600)
    {
        self::$redis->rPush($tags, json_encode($data));
        if($time > 0) self::$redis->expire($tags, $time);
        return $data;
    }

    public static function cacheLPush($tags, $data, $time = 3600)
    {
        self::$redis->lPush($tags, json_encode($data));
        if($time > 0) self::$redis->expire($tags, $time);
        return $data;
    }


    public static function data($tags)
    {
        if(!self::$redis->get($tags)) return [];
        return json_decode(self::$redis->get($tags));
    }

}