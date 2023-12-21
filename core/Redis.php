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

}