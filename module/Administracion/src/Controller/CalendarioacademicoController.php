<?php

declare(strict_types=1);

namespace Administracion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Administracion\Modelo\DAO\CalendarioacademicoDAO;
use Administracion\Formularios\CalendarioacademicoForm;
use Administracion\Modelo\Entidades\Calendarioacademico;

class CalendarioacademicoController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = './public/log/';
    //------------------------------------------------------------------------------

    public function __construct(CalendarioacademicoDAO $dao)
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
    function getFormulario($action = '', $idCalendarioAcademico = 0)
    {
        $form = new CalendarioacademicoForm($action);
        if ($idCalendarioAcademico != 0) {
            $CalendarioOBJ = $this->DAO->getCalendarioacademico($idCalendarioAcademico);
            $form->bind($CalendarioOBJ);
        }
        return $form;
    }
    //------------------------------------------------------------------------------
    public function indexAction()
    {
        $filtro = " calendario_academico.estado != 'Eliminado'";
        return new ViewModel([
            'fetchAll' => $this->DAO->fetchAll($filtro),
        ]);
    }

    //------------------------------------------------------------------------------
    public function registrarAction()
    {
        $fecha =  $this->params()->fromQuery('fecha', '2023-01-01T00:00:00');
        $fecha = explode(".", $fecha);
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];
        //----------------------------------------------------------------------
        $form = new CalendarioacademicoForm('registrar', $fecha[0]);
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $CalendarioOBJ = new Calendarioacademico();
        $form->setInputFilter($CalendarioOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            /*  print_r($form->getMessages());
            return ['form' => $form]; */
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL EVENTO NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        //----------------------------------------------------------------------
        $CalendarioOBJ->exchangeArray($form->getData());
        $CalendarioOBJ->setEstado('Activo');
        $CalendarioOBJ->setRegistradopor($registradopor);
        $CalendarioOBJ->setModificadopor('');
        $CalendarioOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $CalendarioOBJ->setFechahoramod('0000-00-00 00:00:00');
        try {
            $this->DAO->registrar($CalendarioOBJ);
            $this->flashMessenger()->addSuccessMessage('EL EVENTO FUE REGISTRADO EN JIMSOFT');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR EVENTO - CalendarioacademicoController->registrar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EL EVENTO NO FUE REGISTRADO EN JIMSOFT.');
        }
        return $this->redirect()->toUrl('index');
    }
    //------------------------------------------------------------------------------  
    public function editarAction()
    {
        $idCalendarioAcademico = (int) $this->params()->fromQuery('idCalendarioAcademico', 0);
        $form = $this->getFormulario('editar', $idCalendarioAcademico);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $session = $this->getInfoSesion();
                $CalendarioOBJ = new Calendarioacademico($form->getData());
                $CalendarioOBJ->setModificadopor($session['login']);
                $CalendarioOBJ->setFechahoramod(date('Y-m-d H:i:s'));
                try {
                    $this->DAO->editar($CalendarioOBJ);
                    $this->flashMessenger()->addSuccessMessage('EL EVENTO FUE EDITADO EN JIMSOFT');
                    return $this->redirect()->toUrl('index');
                } catch (\Exception $ex) {
                    $msgLog = "\n" . date('Y-m-d H:i:s') . " EDITAR EVENTO - CalendarioacademicoController->registrar \n"
                        . $ex->getMessage()
                        . "\n----------------------------------------------------------------------- \n";
                    $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
                    fwrite($file, $msgLog);
                    fclose($file);
                    $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL EVENTO NO FUE EDITADO EN JIMSOFT.');
                }
            } else {
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE, EL EVENTO NO FUE EDITADO EN JIMSOFT');
                return $this->redirect()->toUrl('index');
            }
        }
        $view = new ViewModel([
            'form' => $form,
            'idCalendarioAcademico' => $idCalendarioAcademico,
        ]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------  
    public function detalleAction()
    {
        $idCalendarioAcademico = (int) $this->params()->fromQuery('idCalendarioAcademico', 0);
        $infoEmpleado = $this->DAO->getEmpleadoDetalle($idCalendarioAcademico);
        $view = new ViewModel(['form' => $infoEmpleado]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------
    public function eliminarAction()
    {
        $idCalendarioAcademico = (int) $this->params()->fromQuery('idCalendarioAcademico', 0);
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];
        $successOK = 0;
        try {
            $this->DAO->eliminar($idCalendarioAcademico, $registradopor);
            $successOK = 1;
            $this->flashMessenger()->addSuccessMessage('EL EVENTO FUE ELIMINADO en JIMSOFT');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " MOVER EVENTO - CalendarioacademicoController->moverevento \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EN JIMSOFT.');
        }
        return new JsonModel(array(
            'successOK' => $successOK,
        ));
    }
    //------------------------------------------------------------------------------  
    public function movereventoAction()
    {
        $idCalendarioAcademico = (int) $this->params()->fromQuery('idCalendarioAcademico', 0);
        $start =  $this->params()->fromQuery('start', '');
        $end =  $this->params()->fromQuery('end', '');
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];
        $successOK = 0;
        try {
            $this->DAO->moverevento($idCalendarioAcademico, $start, $end, $registradopor);
            $successOK = 1;
            $this->flashMessenger()->addSuccessMessage('EL EVENTO FUE MOVIDO: de <b>' . $start . '</b> a <b>' . $end . '</b> en JIMSOFT');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " MOVER EVENTO - CalendarioacademicoController->moverevento \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EN JIMSOFT.');
        }
        return new JsonModel(array(
            'successOK' => $successOK,
        ));
    }
    //------------------------------------------------------------------------------    
}
