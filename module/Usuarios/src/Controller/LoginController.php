<?php

declare(strict_types=1);

namespace Usuarios\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Usuarios\Formularios\LoginForm;
use Usuarios\Modelo\RBAC\IdentityManager;

class LoginController extends AbstractActionController
{

    private ?IdentityManager $identityManager;

    public function __construct(IdentityManager $identityManager)
    {
        $this->identityManager = $identityManager;
    }

    public function loginAction()
    {
        $auth = new AuthenticationService();

        if ($auth->hasIdentity()) {
            return $this->redirect()->toRoute('inicio', ['action' => 'index']);
        }

        $formLogin = new LoginForm('IniciarSesion');
        $viewModel = new ViewModel([
            'formLogin' => $formLogin
        ]);

        $peticion = $this->getRequest();

        if ($peticion->isPost()) {
            $formLogin->setData($peticion->getPost());
            // Verificar CSRF
            if (!$formLogin->isValid()) {
                $this->flashMessenger()->addErrorMessage("Error de validación del formulario.");
                return $this->redirect()->refresh();
            }

            $datos = $formLogin->getData();

            if ($this->identityManager->login($datos['login'], $datos['password'])) {
                return $this->redirect()->toRoute('inicio', ['action' => 'index']);
            } else {
                $this->flashMessenger()->addErrorMessage("Usuario o contraseña incorrectos.");
            }
        }

        $this->layout()->setTemplate('layout/login');
        return $viewModel;
    }

    public function cerrarsesionAction()
    {
        $this->identityManager->logout();
        $this->flashMessenger()->addSuccessMessage("La sesión ha sido cerrada. ¡Hasta pronto!");
        return $this->redirect()->toRoute('login');
    }

    public function noAutorizadoAction()
    {
        return new ViewModel();
    }
}
