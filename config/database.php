<?php
define('DATABASE_CONNECTION','mysql'); // chỉ hỗ trợ mysql, pgsql

define('DATABASE',[
    "mysql" => [
        "default" => [
            "HOST" => '127.0.0.1',
            "PORT" => '3306',
            "DATABASE_NAME" => 'default',
            "USERNAME" => 'root',
            "PASSWORD" => 'root'
        ],
        "production" => [
            "HOST" => '127.0.0.1',
            "PORT" => '3306',
            "DATABASE_NAME" => 'default',
            "USERNAME" => 'root',
            "PASSWORD" => 'root'
        ]
    ],
    "pqsql" => [
        "default" => [
            "HOST" => '127.0.0.1',
            "PORT" => '5432',
            "DATABASE_NAME" => 'default',
            "USERNAME" => 'root',
            "PASSWORD" => 'root'
        ],
        "production" => [
            "HOST" => '127.0.0.1',
            "PORT" => '5432',
            "DATABASE_NAME" => 'default',
            "USERNAME" => 'root',
            "PASSWORD" => 'root'
        ]
    ]
]);


