<?php

declare(strict_types=1);

namespace Usermanager\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Usermanager\Modelo\DAO\RolDAO;
use Usermanager\Formularios\RolForm;
use Usermanager\Formularios\RecursoForm;
use Usermanager\Modelo\Entidades\Rol;
use Usermanager\Modelo\Entidades\Recurso;

class RolController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = './public/log/';
    //------------------------------------------------------------------------------

    public function __construct(RolDAO $dao)
    {
        $this->DAO = $dao;
    }

    //------------------------------------------------------------------------------

    public function getInfoSesion()
    {
        $infoSesion = [
            'idUsuario' => 0,
            'login' => 'SIN INICIO DE SESION'
        ];
        $auth = new AuthenticationService();
        if ($auth->hasIdentity()) {
            $infoSesion['login'] = $auth->getIdentity()->login;
            $infoSesion['idUsuario'] = $auth->getIdentity()->idUsuario;
        }
        return $infoSesion;
    }

    //------------------------------------------------------------------------------
    function getFormulario($action = '', $idRol = 0)
    {
        $form = new RolForm($action);
        if ($idRol != 0) {
            $rolOBJ = $this->DAO->getRol($idRol);
            $form->bind($rolOBJ);
        }
        return $form;
    }
    //------------------------------------------------------------------------------
    public function indexAction()
    {
        $filtro = " roles.estado != 'Eliminado'";
        $filtro2 = " recursos_rbac.estado != 'Eliminado'";
        return new ViewModel([
            'fetchAll' => $this->DAO->fetchAll($filtro),
            'recursos' => $this->DAO->getRecursos($filtro2),
        ]);
    }

    //------------------------------------------------------------------------------
    public function registrarAction()
    {
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];
        //----------------------------------------------------------------------
        $form = new RolForm('registrar');
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $rolOBJ = new Rol();
        $form->setInputFilter($rolOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            /*  print_r($form->getMessages());
            return ['form' => $form]; */
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        //----------------------------------------------------------------------
        $rolOBJ->exchangeArray($form->getData());
        $rolOBJ->setEstado('Activo');
        $rolOBJ->setRegistradopor($registradopor);
        $rolOBJ->setModificadopor('');
        $rolOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $rolOBJ->setFechahoramod('0000-00-00 00:00:00');
        try {
            $this->DAO->registrar($rolOBJ);
            $this->flashMessenger()->addSuccessMessage('EL ROL FUE REGISTRADO EN JIMSOFT');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR ROL - RolController->registrar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EN JIMSOFT.');
        }
        return $this->redirect()->toUrl('index');
    }
    public function registrar2Action()
    {
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];
        //----------------------------------------------------------------------
        $form = new RecursoForm('registrar2');
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $recursoOBJ = new Recurso();
        $form->setInputFilter($recursoOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            /* print_r($form->getMessages());
            return ['form' => $form]; */
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        //----------------------------------------------------------------------
        $recursoOBJ->exchangeArray($form->getData());
        $recursoOBJ->setEstado('Activo');
        $recursoOBJ->setRegistradopor($registradopor);
        $recursoOBJ->setModificadopor('');
        $recursoOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $recursoOBJ->setFechahoramod('0000-00-00 00:00:00');
        try {
            $this->DAO->registrar2($recursoOBJ);
            $this->flashMessenger()->addSuccessMessage('EL RECURSO FUE REGISTRADO EN JIMSOFT');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR RECURSO - RolController->registrar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EN JIMSOFT.');
        }
        return $this->redirect()->toUrl('index');
    }
    //------------------------------------------------------------------------------  
    public function editarAction()
    {
        $idRol = (int) $this->params()->fromQuery('idRol', 0);
        $form = $this->getFormulario('editar', $idRol);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $session = $this->getInfoSesion();
                $rolOBJ = new Rol($form->getData());
                $rolOBJ->setModificadopor($session['login']);
                $rolOBJ->setFechahoramod(date('Y-m-d H:i:s'));
                try {
                    $this->DAO->editar($rolOBJ);
                    $this->flashMessenger()->addSuccessMessage('EL EMPLEADO FUE EDITADO EN JIMSOFT');
                    return $this->redirect()->toUrl('index');
                } catch (\Exception $ex) {
                    $msgLog = "\n" . date('Y-m-d H:i:s') . " EDITAR EMPLEADO - EmpleadoclienteController->registrar \n"
                        . $ex->getMessage()
                        . "\n----------------------------------------------------------------------- \n";
                    $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
                    fwrite($file, $msgLog);
                    fclose($file);
                    $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL EMPLEADO NO FUE EDITADO EN JIMSOFT.');
                }
            } else {
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE, EL EMPLEADO NO FUE EDITADO EN JIMSOFT');
                return $this->redirect()->toUrl('index');
            }
        }
        $view = new ViewModel([
            'form' => $form,
        ]);
        $view->setTerminal(true);
        return $view;
    }
    public function editar2Action()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idRecurso = (int) $this->params()->fromQuery('idRecurso', 0);
            $infoRecurso = $this->DAO->getRecursoDetalle($idRecurso);
            $form = new RecursoForm('editar2');
            $form->setData($infoRecurso);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new RecursoForm('editar2');
        $recursoOBJ = new Recurso();
        $form->setInputFilter($recursoOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            /* print_r($form->getMessages());
            return ['form' => $form];
            exit(); */
            $this->flashMessenger()->addErrorMessage('LA INFORMACION NO ES VALIDA PENE');
            return $this->redirect()->toUrl('index?idRecurso=' . $this->params()->fromPost('idRecurso', 0));
        }
        //----------------------------------------------------------------------
        try {
            $recursoOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $recursoOBJ->setModificadopor($modificadopor);
            $recursoOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->editar2($recursoOBJ);
            $this->flashMessenger()->addSuccessMessage('EL RECURSO FUE EDITADO DE JIMSOFT');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR RECURSO - RolController->editar2 \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL EMPLEADO NO FUE ELIMINADA DE JIMSOFT.');
        }
        return $this->redirect()->toUrl('index?idRecurso=' . $recursoOBJ->getIdRecurso());
    }
    //------------------------------------------------------------------------------  
    public function detalleAction()
    {
        $idRol = (int) $this->params()->fromQuery('idRol', 0);
        $infoEmpleado = $this->DAO->getRolDetalle($idRol);
        $view = new ViewModel(['form' => $infoEmpleado]);
        $view->setTerminal(true);
        return $view;
    }
    public function detalle2Action()
    {
        $idRecurso = (int) $this->params()->fromQuery('idRecurso', 0);
        $infoRecurso = $this->DAO->getRecursoDetalle($idRecurso);
        $view = new ViewModel(['form' => $infoRecurso]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------
    public function eliminarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idRol = (int) $this->params()->fromQuery('idRol', 0);
            $infoCliente = $this->DAO->getRolDetalle($idRol);
            $form = new RolForm('eliminar');
            $form->setData($infoCliente);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new RolForm('eliminar');
        $rolOBJ = new Rol();
        $form->setInputFilter($rolOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            //print_r($form->getMessages());
            //return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE ELIMINACION DEL EMPLEADO NO ES VALIDA');
            return $this->redirect()->toUrl('index?idRol=' . $this->params()->fromPost('idRol', 0));
        }
        //----------------------------------------------------------------------
        try {
            $rolOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $rolOBJ->setModificadopor($modificadopor);
            $rolOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->eliminar($rolOBJ);
            $this->flashMessenger()->addSuccessMessage('EL EMPLEADO FUE ELIMINADA DE JIMSOFT');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ELIMINAR EMPLEADO - ContratolaboralController->eliminar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL EMPLEADO NO FUE ELIMINADA DE JIMSOFT.');
        }
        return $this->redirect()->toUrl('index?idRol=' . $rolOBJ->getIdRol());
    }
    //------------------------------------------------------------------------------  
    public function rolesrcursosAction()
    {
        $idRol = (int) $this->params()->fromQuery('idRol', 0);
        $infoRolesRecursos = $this->DAO->getRolesRecursos($idRol);
        $view = new ViewModel([
            'fetchAll' => $infoRolesRecursos,
            'idRol' => $idRol,
        ]);
        $view->setTerminal(true);
        return $view;
    }
    public function addrecursoAction()
    {
        $idRol = (int) $this->params()->fromQuery('idRol', 0);
        $filtro = "recursos_rbac.idRecurso NOT IN (SELECT recursorbac_rol.idRecurso FROM recursorbac_rol WHERE recursorbac_rol.idRol = $idRol)";
        $getRecursos = $this->DAO->getRecursosSelect($filtro);
        $view = new ViewModel([
            'recursos' => $getRecursos,
            'idRol' => $idRol,
        ]);
        $view->setTerminal(true);
        return $view;
    }
    public function insertarRecursoRolAction()
    {
        $check_lista = $this->params()->fromPost('check_lista', array());
        $idRol = (int) $this->params()->fromPost('idRol', 0);
        if (!empty($check_lista)) {
            try {
                $this->DAO->insertarRecursoRol($idRol, $check_lista);
                $this->flashMessenger()->addSuccessMessage('LOS RECURSOS FUERON ASIGNADOS AL ROL');
            } catch (\Exception $ex) {
                $msgLog = "\n" . date('Y-m-d H:i:s') . " Usermanager RolController->insertarRecursoRol \n"
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
    //------------------------------------------------------------------------------   
    public function eliminarRecursoRolAction()
    {
        $exito = 0;
        $idRol = (int) $this->params()->fromQuery('idRol', 0);
        $idRecurso = (int) $this->params()->fromQuery('idRecurso', 0);
        if ($idRol != 0 &&  $idRecurso  != 0) {
            try {
                $exito = $this->DAO->eliminarRecusoRol($idRol, $idRecurso);
            } catch (\Exception $ex) {
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE: ' . $ex);
            }
        }
        return new JsonModel(array(
            'exito' => $exito,
            'idRol' => $idRol,
        ));
    }
    //------------------------------------------------------------------------------    
}
