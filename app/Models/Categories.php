<?php
namespace App\Models;
use App\Core\Database;
use App\Core\Model\Model;

class Categories extends Model {
    protected static $tableName = 'categories';
    protected static $times_auto = false;
    protected static $date_create = "date_created";
    protected static $date_update = "date_update";
    protected static $field = [
        'name',
        'view'
    ];

    public static function index(){
        Categories::first();
    }

    public static function store(){
        echo 'store';
    }
}