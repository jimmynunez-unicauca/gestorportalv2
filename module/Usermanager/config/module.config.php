<?php

namespace Usermanager;

return [
    'router' => [
        'routes' => [
            'usermanager' => [
                'type' => \Laminas\Router\Http\Literal::class,
                'options' => [
                    'route' => '/usermanager',
                    'defaults' => [
                        'controller' => Controller\EmpleadoclienteController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'empleadocliente' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/empleadocliente/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\EmpleadoclienteController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'acceso' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/acceso/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\AccesoController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'perfil' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/perfil/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\PerfilController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'rol' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/rol/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\RolController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'empleadocliente' => __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ]
    ],
];
