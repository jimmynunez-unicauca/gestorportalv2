<?php

namespace Formularios;

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
                Modelo\DAO\AcademicaDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\AcademicaDAO($dbAdapter);
                },
                Modelo\DAO\CentroposgradoDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\CentroposgradoDAO($dbAdapter);
                },
                Modelo\DAO\ViceadminDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\ViceadminDAO($dbAdapter);
                },
                Modelo\DAO\ViceinvestDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\ViceinvestDAO($dbAdapter);
                },
                Modelo\DAO\DaeempreDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\DaeempreDAO($dbAdapter);
                },
                Modelo\DAO\DaepropiedadDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\DaepropiedadDAO($dbAdapter);
                },
                Modelo\DAO\RectoriaDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\RectoriaDAO($dbAdapter);
                },
                Modelo\DAO\AgrariaDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\AgrariaDAO($dbAdapter);
                },
                Modelo\DAO\FchsDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\FchsDAO($dbAdapter);
                },
                Modelo\DAO\ArtesDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\ArtesDAO($dbAdapter);
                },
                Modelo\DAO\UserformDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\UserformDAO($dbAdapter);
                },
                Modelo\DAO\EgresadosDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\EgresadosDAO($dbAdapter);
                },
                Modelo\DAO\PqrsfDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\PqrsfDAO($dbAdapter);
                },
                Modelo\DAO\ContablesDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\ContablesDAO($dbAdapter);
                },
                Modelo\DAO\EmisoraDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\EmisoraDAO($dbAdapter);
                },
                Modelo\DAO\FietDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\FietDAO($dbAdapter);
                },
                Modelo\DAO\OcdiDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\OcdiDAO($dbAdapter);
                },
                Modelo\DAO\RendicioncuentasDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\RendicioncuentasDAO($dbAdapter);
                },
                Modelo\DAO\FicDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\FicDAO($dbAdapter);
                },
                Modelo\DAO\OriiDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\OriiDAO($dbAdapter);
                },
                Modelo\DAO\FacnedDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\FacnedDAO($dbAdapter);
                },
                Modelo\DAO\FderechoDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\FderechoDAO($dbAdapter);
                },
                Modelo\DAO\FsaludDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\FsaludDAO($dbAdapter);
                },
                Modelo\DAO\SecretariageneralDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\SecretariageneralDAO($dbAdapter);
                },
                Modelo\DAO\ComarcaDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\ComarcaDAO($dbAdapter);
                },
                Modelo\DAO\ConflictointeresDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\ConflictointeresDAO($dbAdapter);
                },
                Modelo\DAO\UnisaludDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\UnisaludDAO($dbAdapter);
                },
                Modelo\DAO\UnisaludrendicioncuentasDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\UnisaludrendicioncuentasDAO($dbAdapter);
                },
                Modelo\DAO\PfiDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\PfiDAO($dbAdapter);
                },
                Modelo\DAO\PfiformDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\PfiformDAO($dbAdapter);
                },
                Modelo\DAO\ConvocatoriaDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\ConvocatoriaDAO($dbAdapter);
                },
            ],
        ];
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                Controller\AcademicaController::class => function ($container) {
                    return new Controller\AcademicaController($container->get(Modelo\DAO\AcademicaDAO::class));
                },
                Controller\CentroposgradoController::class => function ($container) {
                    return new Controller\CentroposgradoController($container->get(Modelo\DAO\CentroposgradoDAO::class));
                },
                Controller\ViceadminController::class => function ($container) {
                    return new Controller\ViceadminController($container->get(Modelo\DAO\ViceadminDAO::class));
                },
                Controller\ViceinvestController::class => function ($container) {
                    return new Controller\ViceinvestController($container->get(Modelo\DAO\ViceinvestDAO::class));
                },
                Controller\DaeempreController::class => function ($container) {
                    return new Controller\DaeempreController($container->get(Modelo\DAO\DaeempreDAO::class));
                },
                Controller\DaepropiedadController::class => function ($container) {
                    return new Controller\DaepropiedadController($container->get(Modelo\DAO\DaepropiedadDAO::class));
                },
                Controller\RectoriaController::class => function ($container) {
                    return new Controller\RectoriaController($container->get(Modelo\DAO\RectoriaDAO::class));
                },
                Controller\AgrariaController::class => function ($container) {
                    return new Controller\AgrariaController($container->get(Modelo\DAO\AgrariaDAO::class));
                },
                Controller\FchsController::class => function ($container) {
                    return new Controller\FchsController($container->get(Modelo\DAO\FchsDAO::class));
                },
                Controller\ArtesController::class => function ($container) {
                    return new Controller\ArtesController($container->get(Modelo\DAO\ArtesDAO::class));
                },
                Controller\UserformController::class => function ($container) {
                    return new Controller\UserformController($container->get(Modelo\DAO\UserformDAO::class));
                },
                Controller\EgresadosController::class => function ($container) {
                    return new Controller\EgresadosController($container->get(Modelo\DAO\EgresadosDAO::class));
                },
                Controller\PqrsfController::class => function ($container) {
                    return new Controller\PqrsfController($container->get(Modelo\DAO\PqrsfDAO::class));
                },
                Controller\ContablesController::class => function ($container) {
                    return new Controller\ContablesController($container->get(Modelo\DAO\ContablesDAO::class));
                },
                Controller\EmisoraController::class => function ($container) {
                    return new Controller\EmisoraController($container->get(Modelo\DAO\EmisoraDAO::class));
                },
                Controller\FietController::class => function ($container) {
                    return new Controller\FietController($container->get(Modelo\DAO\FietDAO::class));
                },
                Controller\OcdiController::class => function ($container) {
                    return new Controller\OcdiController($container->get(Modelo\DAO\OcdiDAO::class));
                },
                Controller\RendicioncuentasController::class => function ($container) {
                    return new Controller\RendicioncuentasController($container->get(Modelo\DAO\RendicioncuentasDAO::class));
                },
                Controller\FicController::class => function ($container) {
                    return new Controller\FicController($container->get(Modelo\DAO\FicDAO::class));
                },
                Controller\OriiController::class => function ($container) {
                    return new Controller\OriiController($container->get(Modelo\DAO\OriiDAO::class));
                },
                Controller\FacnedController::class => function ($container) {
                    return new Controller\FacnedController($container->get(Modelo\DAO\FacnedDAO::class));
                },
                Controller\FderechoController::class => function ($container) {
                    return new Controller\FderechoController($container->get(Modelo\DAO\FderechoDAO::class));
                },
                Controller\FsaludController::class => function ($container) {
                    return new Controller\FsaludController($container->get(Modelo\DAO\FsaludDAO::class));
                },
                Controller\SecretariageneralController::class => function ($container) {
                    return new Controller\SecretariageneralController($container->get(Modelo\DAO\SecretariageneralDAO::class));
                },
                Controller\ComarcaController::class => function ($container) {
                    return new Controller\ComarcaController($container->get(Modelo\DAO\ComarcaDAO::class));
                },
                Controller\ConflictointeresController::class => function ($container) {
                    return new Controller\ConflictointeresController($container->get(Modelo\DAO\ConflictointeresDAO::class));
                },
                Controller\UnisaludController::class => function ($container) {
                    return new Controller\UnisaludController($container->get(Modelo\DAO\UnisaludDAO::class));
                },
                Controller\UnisaludrendicioncuentasController::class => function ($container) {
                    return new Controller\UnisaludrendicioncuentasController($container->get(Modelo\DAO\UnisaludrendicioncuentasDAO::class));
                },
                Controller\PfiController::class => function ($container) {
                    return new Controller\PfiController($container->get(Modelo\DAO\PfiDAO::class));
                },
                Controller\PfiformController::class => function ($container) {
                    return new Controller\PfiformController($container->get(Modelo\DAO\PfiformDAO::class));
                },
                Controller\ConvocatoriaController::class => function ($container) {
                    return new Controller\ConvocatoriaController(
                        $container->get(Modelo\DAO\ConvocatoriaDAO::class),
                        $container->get(Modelo\DAO\PfiDAO::class)
                    );
                },
            ],
        ];
    }
}
