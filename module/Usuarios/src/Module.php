<?php

namespace Usuarios;

use Laminas\Mvc\ModuleRouteListener;
use Laminas\Mvc\MvcEvent;
use Laminas\Session\SessionManager;
use Laminas\Session\Config\SessionConfig;
use Laminas\Session\Container;
use Laminas\Session\Validator;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\Authentication\AuthenticationService;

class Module implements ConfigProviderInterface
{

    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $this->bootstrapSession($e);
        $eventManager->attach(\Zend\Mvc\MvcEvent::EVENT_DISPATCH, array($this, 'validarSession'));
        $eventManager->attach(\Zend\Mvc\MvcEvent::EVENT_ROUTE, array($this, 'verificarPermisos'));
    }

    public function bootstrapSession(MvcEvent $e)
    {
        $session = $e->getApplication()->getServiceManager()->get(SessionManager::class);
        $session->start();

        $container = new Container('initialized');

        if (isset($container->init)) {
            return;
        }

        $serviceManager = $e->getApplication()->getServiceManager();
        $request = $serviceManager->get('Request');

        $session->regenerateId(true);
        $container->init = 1;
        $container->remoteAddr = $request->getServer()->get('REMOTE_ADDR');
        $container->httpUserAgent = $request->getServer()->get('HTTP_USER_AGENT');

        $config = $serviceManager->get('Config');
        if (!isset($config['session'])) {
            return;
        }

        $sessionConfig = $config['session'];

        if (!isset($sessionConfig['validators'])) {
            return;
        }

        $chain = $session->getValidatorChain();

        foreach ($sessionConfig['validators'] as $validator) {
            switch ($validator) {
                case Validator\HttpUserAgent::class:
                    $validator = new $validator($container->httpUserAgent);
                    break;
                case Validator\RemoteAddr::class:
                    $validator = new $validator($container->remoteAddr);
                    break;
                default:
                    $validator = new $validator();
                    break;
            }

            $chain->attach('session.validate', array($validator, 'isValid'));
        }
    }

    //------------------------------------------------------------------------------

    public function validarSession(MvcEvent $e)
    {
        $controlador = $e->getRouteMatch()->getParam('controller');
        $auth = new AuthenticationService();
        if (!$auth->hasIdentity() && $controlador != 'Usuarios\Controller\LoginController') {
            //            echo '<br><br> F U E R A <br><br>';
            $url = $e->getRouter()->assemble(array(), array('name' => 'login'));
            $response = $e->getResponse();
            $response->getHeaders()->addHeaderLine('Location', $url);
            $response->sendHeaders();
            exit();
        }
    }

    //------------------------------------------------------------------------------
    public function verificarPermisos(MvcEvent $event)
    {
        $accionesPermitidas = ['noAutorizado', 'cerrarsesion'];
        $superUsuarios = [1];
        $authService = new AuthenticationService();
        if ($authService->hasIdentity()) {
            $rol = $authService->getIdentity()->login;
            $idUsuario = $authService->getIdentity()->idUsuario;
            $parametrosURL = $event->getRouteMatch()->getParams();
            $controlador = $parametrosURL['controller'];
            $accion = $parametrosURL['action'];
            $metodo = $event->getRequest()->getMethod();
            $permiso = $controlador . '.' . $accion . ':' . $metodo;
            /* echo "<pre>$permiso</pre>"; */
            if (in_array($idUsuario, $superUsuarios)) {
                return;
            }
            if (in_array($accion, $accionesPermitidas)) {
                return;
            }
            if ($controlador == 'Inicio\Controller\BandejaController' && $accion == 'index') {
                return;
            }
            $container = new Container();
            $rbac = $container->rbac;
            if (isset($container->rbac)) {
                if (!$rbac->isGranted($rol, $permiso)) {
                    error_log("El ROL $rol NO TIENE PERMISO PARA $permiso");
                    $url = $event->getRouter()->assemble([], ['name' => 'no-autorizado']);
                    $response = $event->getResponse();
                    $response->getHeaders()->addHeaderLine('Location', $url);
                    $response->sendHeaders();
                    exit();
                }
            } else {
                error_log("(Error RBAC) El ROL $rol NO TIENE PERMISO PARA $permiso");
                $url = $event->getRouter()->assemble([], ['name' => 'no-autorizado']);
                $response = $event->getResponse();
                $response->getHeaders()->addHeaderLine('Location', $url);
                $response->sendHeaders();
                exit();
            }
        }
    }
    //------------------------------------------------------------------------------

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                SessionManager::class => function ($container) {
                    $config = $container->get('config');
                    if (!isset($config['session'])) {
                        $sessionManager = new SessionManager();
                        Container::setDefaultManager($sessionManager);
                        return $sessionManager;
                    }

                    $session = $config['session'];

                    $sessionConfig = null;
                    if (isset($session['config'])) {
                        $class = isset($session['config']['class']) ? $session['config']['class'] : SessionConfig::class;

                        $options = isset($session['config']['options']) ? $session['config']['options'] : [];

                        $sessionConfig = new $class();
                        $sessionConfig->setOptions($options);
                    }

                    $sessionStorage = null;
                    if (isset($session['storage'])) {
                        $class = $session['storage'];
                        $sessionStorage = new $class();
                    }

                    $sessionSaveHandler = null;
                    if (isset($session['save_handler'])) {
                        // class should be fetched from service manager
                        // since it will require constructor arguments
                        $sessionSaveHandler = $container->get($session['save_handler']);
                    }

                    $sessionManager = new SessionManager(
                        $sessionConfig,
                        $sessionStorage,
                        $sessionSaveHandler
                    );

                    Container::setDefaultManager($sessionManager);
                    return $sessionManager;
                },
                Modelo\DAO\UsuarioDAO::class => function ($container) {
                    $tableGateway = $container->get(Modelo\DAO\UsuarioDAOGateway::class);
                    return new Modelo\DAO\UsuarioDAO($tableGateway);
                },
                Modelo\DAO\UsuarioDAOGateway::class => function ($container) {
                    $dbAdapter = $container->get('gestorportal_bd');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Modelo\Entidades\Usuario());
                    return new TableGateway('usuario', $dbAdapter, null, $resultSetPrototype);
                },
            ],
        ];
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                Controller\LoginController::class => function ($container) {
                    return new Controller\LoginController($container->get('IdentityManager'));
                }
            ],
        ];
    }
}
