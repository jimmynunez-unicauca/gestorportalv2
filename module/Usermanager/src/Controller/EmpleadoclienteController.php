<?php

declare(strict_types=1);

namespace Usermanager\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Usermanager\Modelo\DAO\EmpleadoclienteDAO;
use Usermanager\Formularios\EmpleadoclienteForm;
use Usermanager\Modelo\Entidades\Empleadocliente;

class EmpleadoclienteController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = './public/log/';
    //------------------------------------------------------------------------------

    public function __construct(EmpleadoclienteDAO $dao)
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
            $infoSesion['idEmpleadoCliente '] = $auth->getIdentity()->idEmpleadoCliente;
        }
        return $infoSesion;
    }

    //------------------------------------------------------------------------------
    function getFormulario($action = '', $idEmpleado = 0)
    {
        $form = new EmpleadoclienteForm($action);
        if ($idEmpleado != 0) {
            $empleadoOBJ = $this->DAO->getEmpleado($idEmpleado);
            $form->bind($empleadoOBJ);
        }
        return $form;
    }
    //------------------------------------------------------------------------------
    public function indexAction()
    {
        $filtro = " empleadocliente.estado != 'Eliminado'";
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
        $form = new EmpleadoclienteForm('registrar');
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $empleadoOBJ = new Empleadocliente();
        $form->setInputFilter($empleadoOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            /*  print_r($form->getMessages());
            return ['form' => $form]; */
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL EMPLEADO NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        //----------------------------------------------------------------------
        $empleadoOBJ->exchangeArray($form->getData());
        $empleadoOBJ->setEstado('Activo');
        $empleadoOBJ->setRegistradopor($registradopor);
        $empleadoOBJ->setModificadopor('');
        $empleadoOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $empleadoOBJ->setFechahoramod('0000-00-00 00:00:00');
        try {
            $this->DAO->registrar($empleadoOBJ);
            $this->flashMessenger()->addSuccessMessage('EL EMPLEADO FUE REGISTRADO EN JIMSOFT');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR EMPLEADO - EmpleadoclienteController->registrar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EL EMPLEADO NO FUE REGISTRADO EN JIMSOFT.');
        }
        return $this->redirect()->toUrl('index');
    }
    //------------------------------------------------------------------------------  
    public function editarAction()
    {
        $idEmpleado = (int) $this->params()->fromQuery('idEmpleado', 0);
        $form = $this->getFormulario('editar', $idEmpleado);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $session = $this->getInfoSesion();
                $empleadoOBJ = new Empleadocliente($form->getData());
                $empleadoOBJ->setModificadopor($session['login']);
                $empleadoOBJ->setFechahoramod(date('Y-m-d H:i:s'));
                try {
                    $this->DAO->editar($empleadoOBJ);
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
    //------------------------------------------------------------------------------  
    public function detalleAction()
    {
        $idEmpleado = (int) $this->params()->fromQuery('idEmpleado', 0);
        $infoEmpleado = $this->DAO->getEmpleadoDetalle($idEmpleado);
        $view = new ViewModel(['form' => $infoEmpleado]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------
    public function eliminarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idEmpleadoCliente = (int) $this->params()->fromQuery('idEmpleado', 0);
            $infoCliente = $this->DAO->getEmpleadoDetalle($idEmpleadoCliente);
            $form = new EmpleadoclienteForm('eliminar');
            $form->setData($infoCliente);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new EmpleadoclienteForm('eliminar');
        $empleadoOBJ = new Empleadocliente();
        $form->setInputFilter($empleadoOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            //print_r($form->getMessages());
            //return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE ELIMINACION DEL EMPLEADO NO ES VALIDA');
            return $this->redirect()->toUrl('index?idEmpleadoCliente=' . $this->params()->fromPost('idEmpleadoCliente', 0));
        }
        //----------------------------------------------------------------------
        try {
            $empleadoOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $empleadoOBJ->setModificadopor($modificadopor);
            $empleadoOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->eliminar($empleadoOBJ);
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
        return $this->redirect()->toUrl('index?idEmpleadoCliente=' . $empleadoOBJ->getIdEmpleadoCliente());
    }
    //------------------------------------------------------------------------------  
    public function existeidentificacionAction()
    {
        $error = 0;
        $existe = 1;
        $identificacion = $this->params()->fromQuery('identificacion', '');
        if ($identificacion != '') {
            $existe = $this->DAO->existeIdentificacion($identificacion);
        } else {
            $error = 1;
        }
        return new JsonModel(array(
            'error' => $error,
            'existe' => $existe,
            'identificacion' => $identificacion,
        ));
    }
    //------------------------------------------------------------------------------   
}
