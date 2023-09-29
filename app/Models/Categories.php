<?php
namespace App\Models;
use App\Core\Database;
use App\Core\Model\Model;

class Categories extends Model {
    protected static $tableName = 'categories';
    protected static $field = [
        'name',
        'view'
    ];

    public static function index(){
        echo 'index';
    }

    public static function store(){
        echo 'store';
    }
}