<?php

/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */
//------------------------------------------------------------------------------
/* $host = '190.90.224.252';
$puerto = '6303'; */
//$host = '10.220.1.100';
//$puerto = '3306';
//$servidor = $host . ':' . $puerto;
//------------------------------------------------------------------------------
$host = '10.220.1.100';
$servidor = $host; 
//------------------------------------------------------------------------------
return [
    'db' => [
        /* 'driver' => 'Pdo',
        'dsn' => "mysql:dbname=gestorportal_bd;host=$servidor;charset=utf8", */
        'adapters' => [
            'gestorportal_bd' => [
                'driver' => 'Pdo',
                'dsn' => "mysql:dbname=test_gestor;host=$servidor",
                'driver_options' => [
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
                ],
            ],
            'portal_db' => [
                'driver' => 'Pdo',
                'dsn' => "mysql:dbname=test_portal;host=$servidor",
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
