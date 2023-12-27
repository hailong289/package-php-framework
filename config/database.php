<?php

define('DATABASE',[
    "mysql" => [
        "default" => [
            "HOST" => '127.0.0.1',
            "PORT" => '3306',
            "DATABASE_NAME" => 'blog',
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
            'timeout' => 0,
            'reserved' => null,
            'retryInterval' => 0,
            'readTimeout' => 0.0
        ],
        'production' => [
            'host' => '127.0.0.1',
            'password' => null,
            'port' =>  6379,
            'timeout' => 0,
            'reserved' => null,
            'retryInterval' => 0,
            'readTimeout' => 0.0
        ],
    ]
]);


