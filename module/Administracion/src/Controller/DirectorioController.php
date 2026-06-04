<?php

declare(strict_types=1);

namespace Administracion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Administracion\Modelo\DAO\DirectorioDAO;
use Administracion\Formularios\DirectorioForm;
use Administracion\Modelo\Entidades\Directorio;

class DirectorioController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = './public/log/';
    //------------------------------------------------------------------------------

    public function __construct(DirectorioDAO $dao)
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
        $dependencias = $this->DAO->getDependencias();
        $depSelect = array();
        foreach ($dependencias as $dep) {
            $depSelect[$dep['idDependencia']] = $dep['dependencia'];
        }
        //----------------------------------------------------------------------
        $form = new DirectorioForm('registrar', $depSelect);
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $dirOBJ = new Directorio();
        $form->setInputFilter($dirOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            print_r($form->getMessages());
            return ['form' => $form];
            exit();
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL USUARIO NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        $dirOBJ->exchangeArray($form->getData());
        $dirOBJ->setEstado('Activo');
        $dirOBJ->setRegistradopor($registradopor);
        $dirOBJ->setModificadopor('');
        $dirOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $dirOBJ->setFechahoramod('0000-00-00 00:00:00');
        try {
            $this->DAO->registrar($dirOBJ);
            $this->flashMessenger()->addSuccessMessage('EL USUARIO FUE REGISTRADO EN GESTORPORTALV2');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR USUARIO - DirectorioController->registrar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EL USUARIO NO FUE REGISTRADO EN GESTORPORTALV2.');
        }
        return $this->redirect()->toUrl('index');
    }
    //------------------------------------------------------------------------------  
    public function editarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idDI = (int) $this->params()->fromQuery('idDI', 0);
            $infoDirectorio = $this->DAO->getDirectorioDetalle($idDI);
            $dependencias = $this->DAO->getDependencias();
            $depSelect = array();
            foreach ($dependencias as $dep) {
                $depSelect[$dep['idDependencia']] = $dep['dependencia'];
            }
            $form = new DirectorioForm('editar', $depSelect);
            $form->setData($infoDirectorio);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new DirectorioForm('editar');
        $clOBJ = new Directorio();
        $form->setInputFilter($clOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            /*  print_r($form->getMessages());
            return ['form' => $form]; */
            $this->flashMessenger()->addErrorMessage('LA INFORMACION A GUARDAR NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        //----------------------------------------------------------------------
        try {
            $clOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $clOBJ->setModificadopor($modificadopor);
            $clOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->editar($clOBJ);
            $this->flashMessenger()->addSuccessMessage('LA INFORMACION DEL USUARIO FUE ACTUALIZADA');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ACTUALIZAR CONTRATO LABORAL - DirectorioController->editar \n"
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
        $idDI = (int) $this->params()->fromQuery('idDI', 0);
        $infoEmpleado = $this->DAO->getDirectorioDetalle($idDI);
        $view = new ViewModel(['form' => $infoEmpleado]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------
    public function eliminarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idDI = (int) $this->params()->fromQuery('idDI', 0);
            $infoLven = $this->DAO->getDirectorioDetalle($idDI);
            $dependencias = $this->DAO->getDependencias();
            $depSelect = array();
            foreach ($dependencias as $dep) {
                $depSelect[$dep['idDependencia']] = $dep['dependencia'];
            }
            $form = new DirectorioForm('eliminar', $depSelect);
            $form->setData($infoLven);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new DirectorioForm('eliminar');
        $dirOBJ = new Directorio();
        $form->setInputFilter($dirOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            //print_r($form->getMessages());
            //return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE ELIMINACION DEL USUARIO NO ES VALIDA');
            return $this->redirect()->toUrl('index?idDI=' . $this->params()->fromPost('idDI', 0));
        }
        //----------------------------------------------------------------------
        try {
            $dirOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $dirOBJ->setModificadopor($modificadopor);
            $dirOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->eliminar($dirOBJ);
            $this->flashMessenger()->addSuccessMessage('EL USUARIO FUE ELIMINADA DE GESTORPORTALV2');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ELIMINAR USUARIO - DirectorioController->eliminar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL USUARIO NO FUE ELIMINADA DE GESTORPORTALV2.');
        }
        return $this->redirect()->toUrl('index?idDI=' . $dirOBJ->getIdDI());
    }
    public function activarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idDI = (int) $this->params()->fromQuery('idDI', 0);
            $infoArchivo = $this->DAO->getDirectorioDetalle($idDI);
            $dependencias = $this->DAO->getDependencias();
            $depSelect = array();
            foreach ($dependencias as $dep) {
                $depSelect[$dep['idDependencia']] = $dep['dependencia'];
            }
            $form = new DirectorioForm('activar', $depSelect);
            $form->setData($infoArchivo);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new DirectorioForm('activar');
        $dirOBJ = new Directorio();
        $form->setInputFilter($dirOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            //print_r($form->getMessages());
            //return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE ELIMINACION DEL USUARIO NO ES VALIDA');
            return $this->redirect()->toUrl('index?idDI=' . $this->params()->fromPost('idDI', 0));
        }
        //----------------------------------------------------------------------
        try {
            $dirOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $dirOBJ->setModificadopor($modificadopor);
            $dirOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->activar($dirOBJ);
            $this->flashMessenger()->addSuccessMessage('EL USUARIO FUE RECUPERADO DE GESTORPORTAL');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ELIMINAR USUARIO - DirectorioController->eliminar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL USUARIO NO FUE ELIMINADA DE GESTORPORTAL.');
        }
        return $this->redirect()->toUrl('index?idDI=' . $dirOBJ->getidDI());
    }
    //------------------------------------------------------------------------------
}
