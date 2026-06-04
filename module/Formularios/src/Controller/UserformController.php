<?php

declare(strict_types=1);

namespace Formularios\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Formularios\Formularios\UserForm;
use Formularios\Formularios\DepForm;
use Formularios\Modelo\Entidades\User;
use Formularios\Modelo\Entidades\Dep;
use Formularios\Modelo\DAO\UserformDAO;

class UserformController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = './public/log/';
    //------------------------------------------------------------------------------

    public function __construct(UserformDAO $dao)
    {
        $this->DAO = $dao;
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
            'userAll' => $this->DAO->userAll($filtro),
        ]);
    }
    public function indexDependenciaAction()
    {
        $filtro = "";
        return new ViewModel([
            'depenAll' => $this->DAO->depenAll($filtro),
        ]);
    }
    public function userDepAction()
    {
        $filtro = "";
        return new ViewModel([
            'userDepAll' => $this->DAO->userDepAll($filtro),
        ]);
    }

    //------------------------------------------------------------------------------  
    public function detalleAction()
    {
        $id = (int) $this->params()->fromQuery('id', 0);
        $infoForm = $this->DAO->getUserDetalle($id);
        $view = new ViewModel(['form' => $infoForm]);
        $view->setTerminal(true);
        return $view;
    }
    public function detalledepAction()
    {
        $id = (int) $this->params()->fromQuery('id', 0);
        $infoForm = $this->DAO->getDepDetalle($id);
        $view = new ViewModel(['form' => $infoForm]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------ 
    public function userformAction()
    {
        $id = (int) $this->params()->fromQuery('id', 0);
        $fetchAll = $this->DAO->getUserForm($id);
        $view = new ViewModel([
            'fetchAll' => $fetchAll,
            'idform_user' => $id,
        ]);
        $view->setTerminal(true);
        return $view;
    }
    public function adddepAction()
    {
        $id = (int) $this->params()->fromQuery('id', 0);
        $filtro = "form_dependencia.idform_dependencia NOT IN (SELECT user_dependencia.idform_dependencia FROM user_dependencia WHERE user_dependencia.idform_user = $id)";
        $getDep = $this->DAO->getDepSelect($filtro);
        $view = new ViewModel([
            'dep' => $getDep,
            'idform_user' => $id,
        ]);
        $view->setTerminal(true);
        return $view;
    }
    public function insertarUserFormAction()
    {
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];
        $check_lista = $this->params()->fromPost('check_lista', array());
        $idform_user = (int) $this->params()->fromPost('idform_user', 0);
        if (!empty($check_lista)) {
            try {
                $this->DAO->insertarUserForm($idform_user, $check_lista, $registradopor);
                $this->flashMessenger()->addSuccessMessage('LAS DEPENDENCIAS FUERON ASIGNADOS AL CORREO');
            } catch (\Exception $ex) {
                $msgLog = "\n" . date('Y-m-d H:i:s') . " Formularios UserformController->insertarUserForm \n"
                    . $ex->getMessage()
                    . "\n----------------------------------------------------------------------- \n";
                $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
                fwrite($file, $msgLog);
                fclose($file);
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE.');
            }
        } else {
            $this->flashMessenger()->addErrorMessage('RECURSOS NO SELECCIONADOS');
        }
        return $this->redirect()->toUrl('index');
    }
    public function eliminarUserFormAction()
    {
        $exito = 0;
        $idDependencia = (int) $this->params()->fromQuery('idform_dependencia', 0);
        $idUser = (int) $this->params()->fromQuery('idform_user', 0);
        if ($idDependencia != 0 &&  $idUser  != 0) {
            try {
                $exito = $this->DAO->eliminarUserForm($idDependencia, $idUser);
            } catch (\Exception $ex) {
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE: ' . $ex);
            }
        }
        return new JsonModel(array(
            'exito' => $exito,
            'idUser' => $idUser,
        ));
    }
    //------------------------------------------------------------------------------  
    public function registrarAction()
    {
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];
        //----------------------------------------------------------------------
        $form = new UserForm('registrar');
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $userOBJ = new User();
        $form->setInputFilter($userOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            print_r($form->getMessages());
            return ['form' => $form];
            exit();
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL CORREO NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        $userOBJ->exchangeArray($form->getData());
        $userOBJ->setEstado('Activo');
        $userOBJ->setRegistradopor($registradopor);
        $userOBJ->setModificadopor('');
        $userOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $userOBJ->setFechahoramod('0000-00-00 00:00:00');
        try {
            $this->DAO->registrar($userOBJ);
            $this->flashMessenger()->addSuccessMessage('EL CORREO FUE REGISTRADO EN JIMSOFT');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR CORREO - UserformController->registrar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EL CORREO NO FUE REGISTRADO EN JIMSOFT.');
        }
        return $this->redirect()->toUrl('index');
    }
    public function registrardepAction()
    {
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];
        //----------------------------------------------------------------------
        $form = new DepForm('registrardep');
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $depOBJ = new Dep();
        $form->setInputFilter($depOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            print_r($form->getMessages());
            return ['form' => $form];
            exit();
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DE LA DEPENDENCIA NO ES VALIDA');
            return $this->redirect()->toUrl('indexDependencia');
        }
        $depOBJ->exchangeArray($form->getData());
        $depOBJ->setEstado('Activo');
        $depOBJ->setRegistradopor($registradopor);
        $depOBJ->setModificadopor('');
        $depOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $depOBJ->setFechahoramod('0000-00-00 00:00:00');
        try {
            $this->DAO->registrarDep($depOBJ);
            $this->flashMessenger()->addSuccessMessage('LA DEPENDENCIA FUE REGISTRADA EN JIMSOFT');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR DEPENDENCIA - UserformController->registrardep \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! LA DEPENDENCIA NO FUE REGISTRADO EN JIMSOFT.');
        }
        return $this->redirect()->toUrl('indexDependencia');
    }
    //------------------------------------------------------------------------------   
    public function editarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idUser = (int) $this->params()->fromQuery('id', 0);
            $infoUser = $this->DAO->getUserDetalle($idUser);
            $form = new UserForm('editar');
            $form->setData($infoUser);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new UserForm('editar');
        $userOBJ = new User();
        $form->setInputFilter($userOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            /*  print_r($form->getMessages());
            return ['form' => $form]; */
            $this->flashMessenger()->addErrorMessage('LA INFORMACION A GUARDAR NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        //----------------------------------------------------------------------
        try {
            $userOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $userOBJ->setModificadopor($modificadopor);
            $userOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->editar($userOBJ);
            $this->flashMessenger()->addSuccessMessage('LA INFORMACION DEL CORREO FUE ACTUALIZADA');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ACTUALIZAR CORREO - UserController->editar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE!.');
        }
        return $this->redirect()->toUrl('index');
    }
    public function editardepAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idUser = (int) $this->params()->fromQuery('id', 0);
            $infoUser = $this->DAO->getDepDetalle($idUser);
            $form = new DepForm('editardep');
            $form->setData($infoUser);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new DepForm('editar');
        $depOBJ = new Dep();
        $form->setInputFilter($depOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            /*  print_r($form->getMessages());
            return ['form' => $form]; */
            $this->flashMessenger()->addErrorMessage('LA INFORMACION A GUARDAR NO ES VALIDA');
            return $this->redirect()->toUrl('indexDependencia');
        }
        //----------------------------------------------------------------------
        try {
            $depOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $depOBJ->setModificadopor($modificadopor);
            $depOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->editarDep($depOBJ);
            $this->flashMessenger()->addSuccessMessage('LA INFORMACION DEL CORREO FUE ACTUALIZADA');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ACTUALIZAR DEPENDENCIA - UserController->editar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE!.');
        }
        return $this->redirect()->toUrl('indexDependencia');
    }
    //------------------------------------------------------------------------------   
}
