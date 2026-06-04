<?php

return [
    'db' => [
        'adapters' => [
            'gestorportal_bd' => [
                'driver' => 'Pdo',
                'dsn' => "mysql:dbname=test_gestor;host=%HOST%",  # ← marcador
                'driver_options' => [
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
                ],
            ],
            'portal_db' => [
                'driver' => 'Pdo',
                'dsn' => "mysql:dbname=test_portal;host=%HOST%",  # ← marcador
                'driver_options' => [
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
                ],
            ],
        ],
    ],
    'session_containers' => [
        Laminas\Session\Container::class,
    ],
    'session_storage' => [
        'type' => Laminas\Session\Storage\SessionArrayStorage::class,
    ],
    'session_config' => [
        'cache_expire' => 60 * 24 * 30,
        'cookie_httponly' => true,
        'cookie_lifetime' => 86400 * 30,
        'gc_maxlifetime' => 86400 * 30,
        'name' => 'JIMSOFT',
        'remember_me_seconds' => 86400 * 30,
        'use_cookies' => true,
    ],
];
