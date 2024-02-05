<?php

define('DATABASE',[
    "mysql" => [
        "default" => [
            "HOST" => config_env('DB_HOST', '127.0.0.1'),
            "PORT" => config_env('DB_PORT', '3306'),
            "DATABASE_NAME" => config_env('DB_NAME', 'blog'),
            "USERNAME" => config_env('DB_USERNAME', 'root'),
            "PASSWORD" => config_env('DB_PASSWORD', '')
        ],
        "production" => [
            "HOST" => config_env('DB_HOST_PRODUCTION', '127.0.0.1'),
            "PORT" => config_env('DB_PORT_PRODUCTION', '3306'),
            "DATABASE_NAME" => config_env('DB_NAME_PRODUCTION', 'default'),
            "USERNAME" => config_env('DB_USERNAME_PRODUCTION', 'root'),
            "PASSWORD" => config_env('DB_PASSWORD_PRODUCTION', '')
        ]
    ],
    "pgsql" => [
        "default" => [
            "HOST" => config_env('DB_HOST', 'postgres_host'),
            "PORT" => config_env('DB_PORT', '5432'),
            "DATABASE_NAME" => config_env('DB_NAME', 'default'),
            "USERNAME" => config_env('DB_USERNAME', 'postgres'),
            "PASSWORD" => config_env('DB_PASSWORD', 'postgres')
        ],
        "production" => [
            "HOST" => config_env('DB_HOST_PRODUCTION', 'postgres_host'),
            "PORT" => config_env('DB_PORT_PRODUCTION', '5432'),
            "DATABASE_NAME" => config_env('DB_NAME_PRODUCTION', 'default'),
            "USERNAME" => config_env('DB_USERNAME_PRODUCTION', 'postgres'),
            "PASSWORD" => config_env('DB_PASSWORD_PRODUCTION', 'postgres')
        ]
    ],
    'redis' => [
        'default' => [
            'host' => config_env('REDIS_HOST', '127.0.0.1'),
            'port' =>  config_env('REDIS_PORT', '6379'),
            'password' => config_env('REDIS_PASSWORD', null),
            'timeout' => 0,
            'reserved' => null,
            'retryInterval' => 0,
            'readTimeout' => 0.0
        ],
        'production' => [
            'host' => config_env('REDIS_HOST_PRODUCTION', '127.0.0.1'),
            'port' =>  config_env('REDIS_PORT_PRODUCTION', '6379'),
            'password' => config_env('REDIS_PASSWORD_PRODUCTION', null),
            'timeout' => 0,
            'reserved' => null,
            'retryInterval' => 0,
            'readTimeout' => 0.0
        ],
    ]
]);


