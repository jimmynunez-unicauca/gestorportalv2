<?php

namespace Administracion;

return [
    'router' => [
        'routes' => [
            'administracion' => [
                'type' => \Laminas\Router\Http\Literal::class,
                'options' => [
                    'route' => '/administracion',
                    'defaults' => [
                        'controller' => Controller\CalendarioacademicoController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'calendarioacademico' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/calendarioacademico/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\CalendarioacademicoController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'solicitud' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/solicitud/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\SolicitudController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'archivo' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/archivo/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\ArchivoController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'lvmen' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/lvmen/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\LvmenController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'normatividad' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/normatividad/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\NormatividadController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'evento' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/evento/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\EventoController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'cultura' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/cultura/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\CulturaController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'culturacalendario' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/culturacalendario/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\CulturacalendarioController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'cecav' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/cecav/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\CecavController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'cpnormativa' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/cpnormativa/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\CpnormativaController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'cpresultados' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/cpresultados/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\CpresultadosController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'grupo' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/grupo/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\GrupoController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'semillero' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/semillero/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\SemilleroController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'dependencia' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/dependencia/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\DependenciaController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'rectoria' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/rectoria/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\RectoriaController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'contribucionesacademicas' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/contribucionesacademicas/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\ContribucionesacademicasController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'emisora' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/emisora/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\EmisoraController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'directorio' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/directorio/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\DirectorioController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'wpposts' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/wpposts/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\WppostsController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'wplogin' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/wplogin/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\WploginController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'comarca' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/comarca/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\ComarcaController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'pluginunicauca' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/pluginunicauca/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\PluginunicaucaController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'organigrama' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/organigrama/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\OrganigramaController::class,
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
            'calendarioacademico' => __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ]
    ],
];
