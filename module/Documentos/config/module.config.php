<?php

namespace Documentos;

return [
    'router' => [
        'routes' => [
            'documentos' => [
                'type' => \Laminas\Router\Http\Literal::class,
                'options' => [
                    'route' => '/documentos',
                    'defaults' => [
                        'controller' => Controller\CgcController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'cgc' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/cgc/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\CgcController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'ita' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/ita/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\ItaController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'documentosinteres' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/documentosinteres/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\DocumentosinteresController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'orii' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/orii/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\OriiController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'ocdi' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/ocdi/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\OcdiController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'secretariageneral' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/secretariageneral/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\SecretariageneralController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'archivohistorico' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/archivohistorico/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\ArchivohistoricoController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'unisaludrendicioncuentas' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/unisaludrendicioncuentas/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\UnisaludrendicioncuentasController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'unisaludtransparencia' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/unisaludtransparencia/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\UnisaludtransparenciaController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    //Módulos acá
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'academica' => __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ]
    ],
];
