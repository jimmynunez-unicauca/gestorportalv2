<?php

declare(strict_types=1);

namespace Administracion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Administracion\Modelo\DAO\WploginDAO;
use Administracion\Formularios\WploginForm;
use Administracion\Modelo\Entidades\Wplogin;

class WploginController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = './public/log/';
    //------------------------------------------------------------------------------

    public function __construct(WploginDAO $dao)
    {
        $this->DAO = $dao;
    }

    //------------------------------------------------------------------------------

    public function getInfoSesion()
    {
        $infoSesion = [
            'idEmpleadoCliente' => 0,
            'login' => 'SIN INICIO DE SESION'
        ];
        $auth = new AuthenticationService();
        if ($auth->hasIdentity()) {
            $infoSesion['login'] = $auth->getIdentity()->login;
            $infoSesion['idEmpleadoCliente'] = $auth->getIdentity()->idEmpleadoCliente;
        }
        return $infoSesion;
    }

    //------------------------------------------------------------------------------
    public function indexAction()
    {
        $filtro = "";
        return new ViewModel([
            'fetchAll' => $this->DAO->fetchAll($filtro),
        ]);
    }
    //------------------------------------------------------------------------------   
    public function detalleAction()
    {
        $id = (int) $this->params()->fromQuery('id', 0);
        $view = new ViewModel([
            'detalle' => $this->DAO->getWploginDetalle($id),
        ]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------   
}
