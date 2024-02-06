<?php
namespace App\Core;

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

    public function cache($tags, $data, $time = 3600)
    {
        self::$redis->set($tags, serialize($data));
        self::$redis->expire($tags, $time);
        return $data;
    }

    public function data($tags)
    {
        if(!self::$redis->get($tags)) return [];
        return unserialize(self::$redis->get($tags));
    }

}