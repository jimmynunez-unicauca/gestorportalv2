<?php

declare(strict_types=1);

namespace Administracion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Administracion\Modelo\DAO\PluginunicaucaDAO;
use Administracion\Formularios\PluginunicaucaForm;
use Administracion\Modelo\Entidades\Pluginunicauca;

class PluginunicaucaController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = './public/log/';
    //------------------------------------------------------------------------------

    public function __construct(PluginunicaucaDAO $dao)
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
    public function registrarAction()
    {
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];
        //----------------------------------------------------------------------
        $form = new PluginunicaucaForm('registrar');
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $pluginunicaucaOBJ = new Pluginunicauca();
        $form->setInputFilter($pluginunicaucaOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            print_r($form->getMessages());
            return ['form' => $form];
            exit();
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL MODULO NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        if (!$this->DAO->validarNombreModulo($pluginunicaucaOBJ->getNombre_modulo())) {
            $this->flashMessenger()->addErrorMessage('EL NOMBRE DEL MÓDULO YA EXISTE. POR FAVOR USE OTRO NOMBRE.');
            return $this->redirect()->toUrl('index');
        }
        $pluginunicaucaOBJ->exchangeArray($form->getData());
        $pluginunicaucaOBJ->setRegistradopor($registradopor);
        $pluginunicaucaOBJ->setFecha_creacion(date('Y-m-d H:i:s'));
        $pluginunicaucaOBJ->setModificadopor('');
        $pluginunicaucaOBJ->setFecha_actualizacion('0000-00-00 00:00:00');
        try {
            $this->DAO->registrar($pluginunicaucaOBJ);
            $this->flashMessenger()->addSuccessMessage('EL MODULO FUE REGISTRADO EN GESTORPORTALV2');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR MODULO - PluginunicaucaController->registrar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EL MODULO NO FUE REGISTRADO EN GESTORPORTALV2.');
        }
        return $this->redirect()->toUrl('index');
    }
    //------------------------------------------------------------------------------  
    public function editarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);
            $infoPluginunicauca = $this->DAO->getPluginunicauca($id);
            $form = new PluginunicaucaForm('editar');
            $form->setData($infoPluginunicauca);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new PluginunicaucaForm('editar');
        $infoPluginunicauca = new Pluginunicauca();
        $form->setInputFilter($infoPluginunicauca->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            /*  print_r($form->getMessages());
            return ['form' => $form]; */
            $this->flashMessenger()->addErrorMessage('LA INFORMACION A GUARDAR NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        //----------------------------------------------------------------------
        try {
            $infoPluginunicauca->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $infoPluginunicauca->setModificadopor($modificadopor);
            $infoPluginunicauca->setFecha_actualizacion(date('Y-m-d H:i:s'));
            $this->DAO->editar($infoPluginunicauca);
            $this->flashMessenger()->addSuccessMessage('LA INFORMACION DEL PLUGIN   FUE ACTUALIZADA');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ACTUALIZAR PLUGIN - PluginunicaucaController->editar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE!.');
        }
        return $this->redirect()->toUrl('index');
    }

    //------------------------------------------------------------------------------  
    public function detalleAction()
    {
        $id = (int) $this->params()->fromQuery('id', 0);
        $infoPluginunicauca = $this->DAO->getPluginunicauca($id);
        $view = new ViewModel(['form' => $infoPluginunicauca]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------
    public function eliminarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);
            $infoPluginunicauca = $this->DAO->getPluginunicauca($id);
            $form = new PluginunicaucaForm('eliminar');
            $form->setData($infoPluginunicauca);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new PluginunicaucaForm('eliminar');
        $pluginunicaucaOBJ = new Pluginunicauca();
        $form->setInputFilter($pluginunicaucaOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            //print_r($form->getMessages());
            //return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE ELIMINACION DEL PLUGIN NO ES VALIDA');
            return $this->redirect()->toUrl('index?id=' . $this->params()->fromPost('id', 0));
        }
        //----------------------------------------------------------------------
        try {
            $pluginunicaucaOBJ->exchangeArray($form->getData());
            $this->DAO->eliminar($pluginunicaucaOBJ);
            $this->flashMessenger()->addSuccessMessage('EL PLUGIN FUE ELIMINADA DE GESTORPORTALV2');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ELIMINAR PLUGIN - PluginunicaucaController->eliminar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL PLUGIN NO FUE ELIMINADA DE GESTORPORTALV2.');
        }
        return $this->redirect()->toUrl('index?id=' . $pluginunicaucaOBJ->getId());
    }

    //------------------------------------------------------------------------------  
    public function activarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);
            $infoPluginunicauca = $this->DAO->getPluginunicauca($id);

            $form = new PluginunicaucaForm('activar');
            $form->setData($infoPluginunicauca);

            $view = new ViewModel([
                'form' => $form,
                'accion' => 'activar'
            ]);
            $view->setTerminal(true);
            return $view;
        }

        //----------------------------------------------------------------------
        $id = (int) $this->params()->fromPost('id', 0);

        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE MÓDULO NO VÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        try {
            $this->DAO->cambiarEstado($id, 1);
            $this->flashMessenger()->addSuccessMessage('EL MÓDULO FUE ACTIVADO EXITOSAMENTE');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ACTIVAR MÓDULO - PluginunicaucaController->activar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EL MÓDULO NO FUE ACTIVADO.');
        }

        return $this->redirect()->toUrl('index');
    }

    //------------------------------------------------------------------------------  
    public function desactivarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);
            $infoPluginunicauca = $this->DAO->getPluginunicauca($id);

            $form = new PluginunicaucaForm('desactivar');
            $form->setData($infoPluginunicauca);

            $view = new ViewModel([
                'form' => $form,
                'accion' => 'desactivar'
            ]);
            $view->setTerminal(true);
            return $view;
        }

        //----------------------------------------------------------------------
        $id = (int) $this->params()->fromPost('id', 0);

        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE MÓDULO NO VÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        try {
            $this->DAO->cambiarEstado($id, 0);
            $this->flashMessenger()->addSuccessMessage('EL MÓDULO FUE DESACTIVADO EXITOSAMENTE');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " DESACTIVAR MÓDULO - PluginunicaucaController->desactivar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EL MÓDULO NO FUE DESACTIVADO.');
        }

        return $this->redirect()->toUrl('index');
    }
}
