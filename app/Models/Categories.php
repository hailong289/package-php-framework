<?php
namespace App\Models;
use Core\Database;
use Core\Model\Model;

class Categories extends Model {
    protected static $tableName = 'categories';
    protected static $field = [
        'id',
        'name'
    ];

    public static function index(){
//        echo 'categories index';
    }
}