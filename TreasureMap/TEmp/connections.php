<?php

return [
    "connections" => [
        'sqlite' => array(
            'driver' => 'sqlite',
            'database' => __DIR__ . '/../database/production.sqlite',
            'prefix' => '',
        ),
        'mysql' => array(
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'forge',
            'username' => 'forge',
            'password' => '',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ),
        'pgsql' => array(
            'driver' => 'pgsql',
            'host' => 'localhost',
            'database' => 'forge',
            'username' => 'forge',
            'password' => '',
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
        ),
        'sqlsrv' => array(
            'driver' => 'sqlsrv',
            'host' => 'localhost',
            'database' => 'database',
            'username' => 'root',
            'password' => '',
            'prefix' => '',
        )
    ]
];
