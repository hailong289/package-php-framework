<?php
namespace System\Core;
class Redis {
    private static $redis;

    public function __construct()
    {
        self::$redis = Connection::getInstanceRedis(DB_ENVIRONMENT, REDIS_CONNECTION);
    }

    public static function work() {
        self::$redis = Connection::getInstanceRedis(DB_ENVIRONMENT, REDIS_CONNECTION);
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