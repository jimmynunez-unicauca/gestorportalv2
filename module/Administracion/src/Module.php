<?php

namespace Administracion;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;

class Module implements ConfigProviderInterface
{

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                Modelo\DAO\CalendarioacademicoDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\CalendarioacademicoDAO($dbAdapter);
                },
                Modelo\DAO\SolicitudDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\SolicitudDAO($dbAdapter);
                },
                Modelo\DAO\ArchivoDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\ArchivoDAO($dbAdapter);
                },
                Modelo\DAO\LvmenDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\LvmenDAO($dbAdapter);
                },
                Modelo\DAO\NormatividadDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\NormatividadDAO($dbAdapter);
                },
                Modelo\DAO\EventoDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\EventoDAO($dbAdapter);
                },
                Modelo\DAO\CulturaDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\CulturaDAO($dbAdapter);
                },
                Modelo\DAO\CulturacalendarioDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\CulturacalendarioDAO($dbAdapter);
                },
                Modelo\DAO\CecavDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\CecavDAO($dbAdapter);
                },
                Modelo\DAO\CpnormativaDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\CpnormativaDAO($dbAdapter);
                },
                Modelo\DAO\CpresultadosDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\CpresultadosDAO($dbAdapter);
                },
                Modelo\DAO\GrupoDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\GrupoDAO($dbAdapter);
                },
                Modelo\DAO\SemilleroDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\SemilleroDAO($dbAdapter);
                },
                Modelo\DAO\DependenciaDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\DependenciaDAO($dbAdapter);
                },
                Modelo\DAO\RectoriaDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\RectoriaDAO($dbAdapter);
                },
                Modelo\DAO\ContribucionesacademicasDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\ContribucionesacademicasDAO($dbAdapter);
                },
                Modelo\DAO\EmisoraDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\EmisoraDAO($dbAdapter);
                },
                Modelo\DAO\DirectorioDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\DirectorioDAO($dbAdapter);
                },
                Modelo\DAO\WppostsDAO::class => function ($container) {
                    $dbAdapter = $container->get('portal_db');
                    return new Modelo\DAO\WppostsDAO($dbAdapter);
                },
                Modelo\DAO\WploginDAO::class => function ($container) {
                    $dbAdapter = $container->get('portal_db');
                    return new Modelo\DAO\WploginDAO($dbAdapter);
                },
                Modelo\DAO\ComarcaDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\ComarcaDAO($dbAdapter);
                },
                Modelo\DAO\PluginunicaucaDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\PluginunicaucaDAO($dbAdapter);
                },
                Modelo\DAO\OrganigramaDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\OrganigramaDAO($dbAdapter);
                },
            ],
        ];
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                Controller\CalendarioacademicoController::class => function ($container) {
                    return new Controller\CalendarioacademicoController($container->get(Modelo\DAO\CalendarioacademicoDAO::class));
                },
                Controller\SolicitudController::class => function ($container) {
                    return new Controller\SolicitudController($container->get(Modelo\DAO\SolicitudDAO::class));
                },
                Controller\ArchivoController::class => function ($container) {
                    return new Controller\ArchivoController($container->get(Modelo\DAO\ArchivoDAO::class));
                },
                Controller\LvmenController::class => function ($container) {
                    return new Controller\LvmenController($container->get(Modelo\DAO\LvmenDAO::class));
                },
                Controller\NormatividadController::class => function ($container) {
                    return new Controller\NormatividadController($container->get(Modelo\DAO\NormatividadDAO::class));
                },
                Controller\EventoController::class => function ($container) {
                    return new Controller\EventoController($container->get(Modelo\DAO\EventoDAO::class));
                },
                Controller\CulturaController::class => function ($container) {
                    return new Controller\CulturaController($container->get(Modelo\DAO\CulturaDAO::class));
                },
                Controller\CulturacalendarioController::class => function ($container) {
                    return new Controller\CulturacalendarioController($container->get(Modelo\DAO\CulturacalendarioDAO::class));
                },
                Controller\CecavController::class => function ($container) {
                    return new Controller\CecavController($container->get(Modelo\DAO\CecavDAO::class));
                },
                Controller\CpnormativaController::class => function ($container) {
                    return new Controller\CpnormativaController($container->get(Modelo\DAO\CpnormativaDAO::class));
                },
                Controller\CpresultadosController::class => function ($container) {
                    return new Controller\CpresultadosController($container->get(Modelo\DAO\CpresultadosDAO::class));
                },
                Controller\GrupoController::class => function ($container) {
                    return new Controller\GrupoController($container->get(Modelo\DAO\GrupoDAO::class));
                },
                Controller\SemilleroController::class => function ($container) {
                    return new Controller\SemilleroController($container->get(Modelo\DAO\SemilleroDAO::class));
                },
                Controller\DependenciaController::class => function ($container) {
                    return new Controller\DependenciaController($container->get(Modelo\DAO\DependenciaDAO::class));
                },
                Controller\RectoriaController::class => function ($container) {
                    return new Controller\RectoriaController($container->get(Modelo\DAO\RectoriaDAO::class));
                },
                Controller\ContribucionesacademicasController::class => function ($container) {
                    return new Controller\ContribucionesacademicasController($container->get(Modelo\DAO\ContribucionesacademicasDAO::class));
                },
                Controller\EmisoraController::class => function ($container) {
                    return new Controller\EmisoraController($container->get(Modelo\DAO\EmisoraDAO::class));
                },
                Controller\DirectorioController::class => function ($container) {
                    return new Controller\DirectorioController($container->get(Modelo\DAO\DirectorioDAO::class));
                },
                Controller\WppostsController::class => function ($container) {
                    return new Controller\WppostsController($container->get(Modelo\DAO\WppostsDAO::class));
                },
                Controller\WploginController::class => function ($container) {
                    return new Controller\WploginController($container->get(Modelo\DAO\WploginDAO::class));
                },
                Controller\ComarcaController::class => function ($container) {
                    return new Controller\ComarcaController($container->get(Modelo\DAO\ComarcaDAO::class));
                },
                Controller\PluginunicaucaController::class => function ($container) {
                    return new Controller\PluginunicaucaController($container->get(Modelo\DAO\PluginunicaucaDAO::class));
                },
                Controller\OrganigramaController::class => function ($container) {
                    return new Controller\OrganigramaController($container->get(Modelo\DAO\OrganigramaDAO::class));
                },
            ],
        ];
    }
}
