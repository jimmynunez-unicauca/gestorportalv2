<?php

// Credenciales por ambiente
$host = '10.220.1.100';
$nombre_bd = 'test_gestor';
$nombre_bd2 = 'test_portal';
$username = 'portal_test_user';
$password = 'P0rt4l*T3st';

return [
    'db' => [
        'username' => $username,
        'password' => $password,
        'adapters' => [
            'gestorportal_bd' => [
                'username' => $username,
                'password' => $password,
                'dsn' => "mysql:dbname=test_gestor;host=$host",
            ],
            'portal_db' => [
                'username' => $username,
                'password' => $password,
                'dsn' => "mysql:dbname=test_portal;host=$host",
            ],
        ],
    ],

    // ================================================================
    // Módulo Podcast — configuración de subida
    // ================================================================
    'podcast_upload' => [
        /* 'endpoint' => 'http://10.200.2.15/podcast_storage/upload_podcast.php', */
        'endpoint' => 'https://unicauca.edu.co/sitioemisora/podcast_storage/upload_podcast.php',
        'token'    => 'AdminPodcast2026*',
    ],
];
