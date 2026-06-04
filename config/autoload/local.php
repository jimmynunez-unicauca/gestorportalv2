<?php

// Credenciales por ambiente
$host = '10.220.1.100';  # ← IP para desarrollo/preproducción
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
                // Reemplazar el marcador %HOST% con el valor real
                'dsn' => "mysql:dbname=test_gestor;host=$host",
            ],
            'portal_db' => [
                'username' => $username,
                'password' => $password,
                'dsn' => "mysql:dbname=test_portal;host=$host",
            ],
        ],
    ]
];
