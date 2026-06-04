<?php

declare(strict_types=1);

namespace Formularios\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Formularios\Modelo\DAO\PfiDAO;
use Formularios\Formularios\PfiForm;
use Formularios\Modelo\Entidades\Pfi;

class PfiController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = './public/log/';
    //------------------------------------------------------------------------------

    public function __construct(PfiDAO $dao)
    {
        $this->DAO = $dao;
    }

    //------------------------------------------------------------------------------
    function getFormulario($action = '', $idPfi = 0)
    {
        $form = new PfiForm($action);
        if ($idPfi != 0) {
            $pfiOBJ = $this->DAO->getPfi($idPfi);
            $form->bind($pfiOBJ);
        }
        return $form;
    }
    //------------------------------------------------------------------------------

    public function getInfoSesion()
    {
        $infoSesion = [
            'idEmpleadoCliente ' => 0,
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
        $infoEmpleado = $this->DAO->getFormDetalle($id);
        $view = new ViewModel(['form' => $infoEmpleado]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------  
    public function registrarAction()
    {
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];
        //----------------------------------------------------------------------
        $form = new PfiForm('registrar');
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $pfiOBJ = new Pfi();
        $form->setInputFilter($pfiOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            /* print_r($form->getMessages());
            return ['form' => $form];
            exit(); */
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL FORMULARIO NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        $pfiOBJ->exchangeArray($form->getData());
        $pfiOBJ->setActivo('1');
        $pfiOBJ->setCreatedBy($registradopor);
        $pfiOBJ->setCreatedAt(date('Y-m-d H:i:s'));
        $pfiOBJ->setUpdatedAt('0000-00-00 00:00:00');
        try {
            $this->DAO->registrar($pfiOBJ);
            $this->flashMessenger()->addSuccessMessage('EL FORMULARIO FUE REGISTRADO EN GESTORPORTAL');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR FORMULARIO - PfiController->registrar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EL FORMULARIO NO FUE REGISTRADO EN GESTORPORTAL.');
        }
        return $this->redirect()->toUrl('index');
    }
    //------------------------------------------------------------------------------   
    public function editarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);
            $infoPfi = $this->DAO->getFormPfi($id);
            if ($infoPfi && !empty($infoPfi->getHoraLimiteDiaria())) {
                $hora = $infoPfi->getHoraLimiteDiaria();
                // Si tiene segundos (formato H:i:s), quitar los segundos
                if (strlen($hora) == 8 && substr($hora, 2, 1) == ':' && substr($hora, 5, 1) == ':') {
                    $hora = substr($hora, 0, 5); // Quitar :00
                    $infoPfi->setHoraLimiteDiaria($hora);
                }
            }
            $form = new PfiForm('editar');
            $form->bind($infoPfi);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new PfiForm('editar');
        $pfiOBJ = new Pfi();
        $form->setInputFilter($pfiOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            /* print_r($form->getMessages());
            return ['form' => $form];
            exit(); */
            $this->flashMessenger()->addErrorMessage('LA INFORMACION A GUARDAR NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        //----------------------------------------------------------------------
        try {
            $pfiOBJ->exchangeArray($form->getData());
            $pfiOBJ->setUpdatedAt(date('Y-m-d H:i:s'));
            $this->DAO->editar($pfiOBJ);
            $this->flashMessenger()->addSuccessMessage('LA INFORMACION DEL FORMULARIO FUE ACTUALIZADA');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ACTUALIZAR FORMULARIO  - PfiController->editar \n"
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
    public function desactivarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);
            $infoPfi = $this->DAO->getFormDetalle($id);
            $form = new PfiForm('desactivar');
            $form->setData($infoPfi);
            $view = new ViewModel([
                'form' => $form,
                'accion' => 'desactivar'
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $id = (int) $this->params()->fromPost('id_config', 0);

        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE FORMULARIO NO VÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        try {
            $this->DAO->cambiarEstado($id, 0);
            $this->flashMessenger()->addSuccessMessage('EL FORMULARIO FUE DESACTIVADO EXITOSAMENTE');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " DESACTIVAR FORMULARIO - PfiController->desactivar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EL FORMULARIO NO FUE DESACTIVADO.');
        }

        return $this->redirect()->toUrl('index');
    }
    public function activarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);
            $infoPfi = $this->DAO->getFormDetalle($id);

            $form = new PfiForm('activar');
            $form->setData($infoPfi);

            $view = new ViewModel([
                'form' => $form,
                'accion' => 'activar'
            ]);
            $view->setTerminal(true);
            return $view;
        }

        //----------------------------------------------------------------------
        $id = (int) $this->params()->fromPost('id_config', 0);

        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE FORMULARIO NO VÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        try {
            $this->DAO->cambiarEstado($id, 1);
            $this->flashMessenger()->addSuccessMessage('EL FORMULARIO FUE ACTIVADO EXITOSAMENTE');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ACTIVAR FORMULARIO - PfiController->activar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EL FORMULARIO NO FUE ACTIVADO.');
        }

        return $this->redirect()->toUrl('index');
    }
    //------------------------------------------------------------------------------  
    public function eliminarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);
            $infoPfi = $this->DAO->getFormPfi($id);
            if ($infoPfi && !empty($infoPfi->getHoraLimiteDiaria())) {
                $hora = $infoPfi->getHoraLimiteDiaria();
                if (strlen($hora) == 8 && substr($hora, 2, 1) == ':' && substr($hora, 5, 1) == ':') {
                    $hora = substr($hora, 0, 5); // Quitar :00
                    $infoPfi->setHoraLimiteDiaria($hora);
                }
            }
            $form = new PfiForm('eliminar');
            /*  $form->setData($infoPfi); */
            $form->bind($infoPfi);
            $view = new ViewModel([
                'form' => $form,
                'accion' => 'eliminar'
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new PfiForm('eliminar');
        $usOBJ = new Pfi();
        $form->setInputFilter($usOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            print_r($form->getMessages());
            return ['form' => $form];
            exit();
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE ELIMINACION DEL FORMULARIO NO ES VALIDA');
            return $this->redirect()->toUrl('index?id=' . $this->params()->fromPost('id_config', 0));
        }
        //----------------------------------------------------------------------
        try {
            $usOBJ->exchangeArray($form->getData());
            $this->DAO->eliminar($usOBJ);
            $this->flashMessenger()->addSuccessMessage('EL FORMULARIO FUE ELIMINADO DE GESTORPORTAL');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ELIMINAR FORMULARIO - PfiController->eliminar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL FORMULARIO NO FUE ELIMINADO DE GESTORPORTAL.');
        }
        return $this->redirect()->toUrl('index?id=' . $usOBJ->getIdConfig());
    }
    //------------------------------------------------------------------------------   
}
