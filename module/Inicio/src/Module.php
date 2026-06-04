<?php

namespace Inicio;

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
                Modelo\DAO\InicioDAO::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    return new Modelo\DAO\InicioDAO($dbAdapter);
                },
                Modelo\DAO\PortalDAO::class => function ($container) {
                    $dbAdapter = $container->get('portal_db');
                    return new Modelo\DAO\PortalDAO($dbAdapter);
                },
            ],
        ];
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                Controller\BandejaController::class => function ($container) {
                    return new Controller\BandejaController(
                        $container->get(Modelo\DAO\InicioDAO::class),
                        $container->get(Modelo\DAO\PortalDAO::class)
                    );
                },
            ],
        ];
    }
}
