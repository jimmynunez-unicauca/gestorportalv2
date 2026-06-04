<?php

declare(strict_types=1);

namespace Administracion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Administracion\Modelo\DAO\SolicitudDAO;
use Administracion\Formularios\EventoForm;
use Administracion\Modelo\Entidades\Evento;

class SolicitudController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = './public/log/';
    //------------------------------------------------------------------------------

    public function __construct(SolicitudDAO $dao)
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
    function getFormulario($action = '', $idEvento = 0)
    {
        $form = new EventoForm($action);
        if ($idEvento != 0) {
            $eventoOBJ = $this->DAO->getEvento($idEvento);
            $form->bind($eventoOBJ);
        }
        return $form;
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
        $fecha =  $this->params()->fromQuery('fecha', '2023-01-01T00:00:00');
        $fecha = explode(".", $fecha);
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];
        //----------------------------------------------------------------------
        $form = new EventoForm('registrar', $fecha[0]);
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $eventoOBJ = new Evento();
        $form->setInputFilter($eventoOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            /*  print_r($form->getMessages());
            return ['form' => $form]; */
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL EVENTO NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        //----------------------------------------------------------------------
        $eventoOBJ->exchangeArray($form->getData());
        $eventoOBJ->setEstado('Activo');
        $eventoOBJ->setRegistradopor($registradopor);
        $eventoOBJ->setModificadopor('');
        $eventoOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $eventoOBJ->setFechahoramod('0000-00-00 00:00:00');
        try {
            $this->DAO->registrar($eventoOBJ);
            $this->flashMessenger()->addSuccessMessage('EL EVENTO FUE REGISTRADO EN JIMSOFT');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR EVENTO - EventoController->registrar \n"
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
        $idEvento = (int) $this->params()->fromQuery('idEvento', 0);
        $form = $this->getFormulario('editar', $idEvento);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $session = $this->getInfoSesion();
                $eventoOBJ = new Evento($form->getData());
                $eventoOBJ->setModificadopor($session['login']);
                $eventoOBJ->setFechahoramod(date('Y-m-d H:i:s'));
                try {
                    $this->DAO->editar($eventoOBJ);
                    $this->flashMessenger()->addSuccessMessage('EL EVENTO FUE EDITADO EN JIMSOFT');
                    return $this->redirect()->toUrl('index');
                } catch (\Exception $ex) {
                    $msgLog = "\n" . date('Y-m-d H:i:s') . " EDITAR EVENTO - EventoController->registrar \n"
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
            'idEvento' => $idEvento,
        ]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------  
    public function detalleAction()
    {
        $idEvento = (int) $this->params()->fromQuery('idEvento', 0);
        $infoEmpleado = $this->DAO->getEmpleadoDetalle($idEvento);
        $view = new ViewModel(['form' => $infoEmpleado]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------
    public function eliminarAction()
    {
        $idEvento = (int) $this->params()->fromQuery('idEvento', 0);
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];
        $successOK = 0;
        try {
            $this->DAO->eliminar($idEvento, $registradopor);
            $successOK = 1;
            $this->flashMessenger()->addSuccessMessage('EL EVENTO FUE ELIMINADO en JIMSOFT');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " MOVER EVENTO - EventoController->moverevento \n"
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
        $idEvento = (int) $this->params()->fromQuery('idEvento', 0);
        $start =  $this->params()->fromQuery('start', '');
        $end =  $this->params()->fromQuery('end', '');
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];
        $successOK = 0;
        try {
            $this->DAO->moverevento($idEvento, $start, $end, $registradopor);
            $successOK = 1;
            $this->flashMessenger()->addSuccessMessage('EL EVENTO FUE MOVIDO: de <b>' . $start . '</b> a <b>' . $end . '</b> en JIMSOFT');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " MOVER EVENTO - EventoController->moverevento \n"
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
