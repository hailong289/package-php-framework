<?php
define('DB_CONNECTION', 'mysql'); // chỉ hỗ trợ mysql, pgsql
define('DB_ENVIRONMENT', 'default'); // environment
define('DATABASE',[
    "mysql" => [
        "default" => [
            "HOST" => '127.0.0.1',
            "PORT" => '3306',
            "DATABASE_NAME" => 'music',
            "USERNAME" => 'root',
            "PASSWORD" => ''
        ],
        "production" => [
            "HOST" => '127.0.0.1',
            "PORT" => '3306',
            "DATABASE_NAME" => 'default',
            "USERNAME" => 'root',
            "PASSWORD" => 'root'
        ]
    ],
    "pgsql" => [
        "default" => [
            "HOST" => 'postgres_host',
            "PORT" => '5432',
            "DATABASE_NAME" => 'postgres',
            "USERNAME" => 'postgres',
            "PASSWORD" => 'postgres'
        ],
        "production" => [
            "HOST" => '127.0.0.1',
            "PORT" => '5432',
            "DATABASE_NAME" => 'default',
            "USERNAME" => 'root',
            "PASSWORD" => 'root'
        ]
    ],
    'redis' => [ // comming soon
        'default' => [
            'host' => '127.0.0.1',
            'password' => null,
            'port' =>  6379,
            'database' => 0
        ],
        'production' => [
            'host' => '127.0.0.1',
            'password' => null,
            'port' =>  6379,
            'database' => 0
        ],
    ]
]);


