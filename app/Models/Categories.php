<?php
namespace App\Models;
use System\Core\Model;
use System\Core\Redis;

class Categories extends Model {
    protected static $tableName = 'categories';
    protected static $times_auto = false;
    protected static $date_create = "date_created";
    protected static $date_update = "date_update";
    protected static $field = [
        'name',
        'view'
    ];
    protected static $hiddenField = [
        'invalid',
    ];

    public function setAttributeName($value){
        return $value;
    }

    public function getAttributeName($value) {
        return $value;
    }


    public static function index(){
       $redis = Redis::work();
       $key = 'data:categories';
       $category = Redis::data($key);
       if(empty($category)) $category = Redis::cacheRPush($key, Categories::get()->values(), 500);
       return $category;
    }

    public static function store(){
        echo 'store';
    }
}