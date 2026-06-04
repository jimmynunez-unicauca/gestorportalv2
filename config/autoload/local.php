<?php

/**
 * Local Configuration Override
 *
 * This configuration override file is for overriding environment-specific and
 * security-sensitive configuration information. Copy this file without the
 * .dist extension at the end and populate values as needed.
 *
 * NOTE: This file is ignored from Git by default with the .gitignore included
 * in laminas-mvc-skeleton. This is a good practice, as it prevents sensitive
 * credentials from accidentally being committed into version control.
 */
//------------------------------------------------------------------------------
//$username = 'root';
//$password = 'j0s4nDrO.BD*';
//------------------------------------------------------------------------------
/* $username = 'josandroDev';
$password = 'j0s4ndr0.DEV'; */
//------------------------------------------------------------------------------
$username = 'portal_test_user';
$password = 'P0rt4l*T3st';
//------------------------------------------------------------------------------
return [
    'db' => [
        // for primary db adapter that called
        // by $sm->get('Zend\Db\Adapter\Adapter')
        'username' => $username,
        'password' => $password,
        // to allow other adapter to be called by
        // $sm->get('db1') or $sm->get('db2') based on the adapters config.
        'adapters' => [
            'gestorportal_bd' => [
                'username' => 'portal_test_user',
                'password' => 'P0rt4l*T3st',
            ],
            'portal_db' => [
                'username' => 'portal_test_user',
                'password' => 'P0rt4l*T3st',
            ],
        ],
    ]
];
