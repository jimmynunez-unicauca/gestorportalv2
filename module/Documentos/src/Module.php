<?php

namespace Documentos;

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
                Modelo\DAO\CgcDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\CgcDAO($dbAdapter);
                },
                Modelo\DAO\ItaDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\ItaDAO($dbAdapter);
                },
                Modelo\DAO\DocumentosinteresDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\DocumentosinteresDAO($dbAdapter);
                },
                Modelo\DAO\OriiDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\OriiDAO($dbAdapter);
                },
                Modelo\DAO\OcdiDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\OcdiDAO($dbAdapter);
                },
                Modelo\DAO\SecretariageneralDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\SecretariageneralDAO($dbAdapter);
                },
                Modelo\DAO\ArchivohistoricoDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\ArchivohistoricoDAO($dbAdapter);
                },
                Modelo\DAO\UnisaludrendicioncuentasDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\UnisaludrendicioncuentasDAO($dbAdapter);
                },
                Modelo\DAO\UnisaludtransparenciaDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\UnisaludtransparenciaDAO($dbAdapter);
                },
            ],
        ];
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                Controller\CgcController::class => function ($container) {
                    return new Controller\CgcController($container->get(Modelo\DAO\CgcDAO::class));
                },
                Controller\ItaController::class => function ($container) {
                    return new Controller\ItaController($container->get(Modelo\DAO\ItaDAO::class));
                },
                Controller\DocumentosinteresController::class => function ($container) {
                    return new Controller\DocumentosinteresController($container->get(Modelo\DAO\DocumentosinteresDAO::class));
                },
                Controller\OriiController::class => function ($container) {
                    return new Controller\OriiController($container->get(Modelo\DAO\OriiDAO::class));
                },
                Controller\OcdiController::class => function ($container) {
                    return new Controller\OcdiController($container->get(Modelo\DAO\OcdiDAO::class));
                },
                Controller\SecretariageneralController::class => function ($container) {
                    return new Controller\SecretariageneralController($container->get(Modelo\DAO\SecretariageneralDAO::class));
                },
                Controller\ArchivohistoricoController::class => function ($container) {
                    return new Controller\ArchivohistoricoController($container->get(Modelo\DAO\ArchivohistoricoDAO::class));
                },
                Controller\UnisaludrendicioncuentasController::class => function ($container) {
                    return new Controller\UnisaludrendicioncuentasController($container->get(Modelo\DAO\UnisaludrendicioncuentasDAO::class));
                },
                Controller\UnisaludtransparenciaController::class => function ($container) {
                    return new Controller\UnisaludtransparenciaController($container->get(Modelo\DAO\UnisaludtransparenciaDAO::class));
                },
            ],
        ];
    }
}
