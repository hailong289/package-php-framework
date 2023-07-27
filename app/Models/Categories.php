<?php
namespace App\Models;
use Core\Model\Model;

class Categories extends Model {

    public static function table()
    {
        return 'categories';
    }

    public static function fieldTable()
    {
        return '';
    }

    public static function index(){
        self::$DB->enableQueryLog();

        var_dump(Categories::get());
        echo 'categories index';
    }
}