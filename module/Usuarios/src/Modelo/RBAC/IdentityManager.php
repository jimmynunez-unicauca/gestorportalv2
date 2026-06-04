<?php

namespace Usuarios\Modelo\RBAC;

use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\Permissions\Rbac\Role;
use Laminas\Permissions\Rbac\Rbac;
use Laminas\Session\Container;
use Usuarios\Modelo\DAO\RbacDAO;

class IdentityManager
{
    private ?AdapterInterface $adapter;
    private ?RbacDAO $DAO;
    private ?AuthenticationService $auth;

    public function __construct(?AdapterInterface $adapter, ?RbacDAO $DAO)
    {
        $this->adapter = $adapter;
        $this->DAO = $DAO;
        $this->auth = new AuthenticationService();
    }

    public function login(string $username = '', string $password = ''): bool
    {
        $this->adapter->setIdentity($username);
        $this->adapter->setCredential($password);
        $select = $this->adapter->getDbSelect();
        $select->where("estado = 'Activo'");
        $this->auth->setAdapter($this->adapter);
        $result = $this->auth->authenticate();
        if ($result->isValid()) {
            error_log('EL USUARIO ' . $result->getIdentity() . ' HA INICIADO SESION');
            $this->auth->getStorage()->write($this->adapter->getResultRowObject(null, 'password'));
            $this->cargarDatosAutorizacion($result->getIdentity());
            return true;
        } else {
            foreach ($result->getMessages() as $msg) {
                error_log($msg);
            }
        }
        return false;
    }

    public function logout()
    {
        $this->auth->clearIdentity();
        $session = new \Laminas\Session\SessionManager();
        try {
            $session->destroy();
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }

    public function getIdentity(): string
    {
        return $this->auth->getIdentity()->login;
    }

    public function hasIdentity(): bool
    {
        return $this->auth->hasIdentity();
    }

    public function getRbac(): Rbac
    {
        $this->cargarDatosAutorizacion();
        $container = new Container();
        return $container->rbac;
    }

    public function cargarDatosAutorizacion(string $identity): void
    {
        $container = new Container();
        if (isset($container->rbac)) {
            unset($container->rbac);
        }
        $usuarioLogin = $this->adapter->getResultRowObject();
        $recursosrbac = $this->DAO->getRecursosRbacByIdUsuario($usuarioLogin->idUsuario);
        if (count($recursosrbac) > 0) {
            $rbac = new Rbac();
            $rolRbac = new Role(strtolower($identity));
            foreach ($recursosrbac as $recurso) {
                $rolRbac->addPermission($recurso['recurso'] . ':' . $recurso['metodo']);
            }
            $rbac->addRole($rolRbac);
            $container->rbac = $rbac;
        }
    }
}
