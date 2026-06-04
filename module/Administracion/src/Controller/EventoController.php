<?php

declare(strict_types=1);

namespace Administracion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Administracion\Modelo\DAO\EventoDAO;
use Administracion\Formularios\EventoForm;
use Administracion\Modelo\Entidades\Evento;

class EventoController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = './public/log/';
    private $rutaArchivos = '/var/www/html/newportal/archivos/eventos/';
    /* private $rutaArchivos = './../archivos/eventos/'; */
    //------------------------------------------------------------------------------

    public function __construct(EventoDAO $dao)
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
        $filtro = " evento.estado != 'Eliminado'";
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
        /* $form->setInputFilter($eventoOBJ->getInputFilter()); */
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            /*  print_r($form->getMessages());
            return ['form' => $form]; */
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL EVENTO NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        //----------------------------------------------------------------------
        $files = $request->getFiles()->toArray();
        //----------------------------------------------------------------------
        $uploadOK = new \Laminas\Validator\File\UploadFile();
        if (!$uploadOK->isValid($files['imagen'])) {
            $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO PRESENTA ERRORES AL CARGAR AL SERVIDOR');
            return $this->redirect()->toUrl('index');
        }
        if (array_key_exists('imagen', $files)) {
            $ext = pathinfo($files['imagen']['name'], PATHINFO_EXTENSION);
            $filesize = new \Laminas\Validator\File\Size([
                'min' => '250B',
                'max' => '2MB',
            ]);
            if (!$filesize->isValid($files['imagen'])) {
                $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO NO ESTA EN LOS LIMITES PERMITIDOS. <br> MINIMO: 250B  <br> MAXIMO: <b>2MB</b>');
                return $this->redirect()->toUrl('index');
            }
            $extensiones = new \Laminas\Validator\File\Extension(array('extension' => array('jpg,jpeg,png,gif,bmp,svg')));
            if (!$extensiones->isValid($files['imagen'])) {
                $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO NO ES PERMITIDO. <br> ARCHIVOS PERMITIDOS: <br> PDF');
                return $this->redirect()->toUrl('index');
            }
            $filter = new \Laminas\Filter\File\RenameUpload([
                'target' => $this->rutaArchivos . 'EVENTO' . '.' . $ext,
                'randomize' => true,
            ]);
            //----------------------------------------------------------------------
            $upload = $filter->filter($files['imagen']);
            //----------------------------------------------------------------------
            if ($upload['error'] != 0) {
                $this->flashMessenger()->addErrorMessage('NO FUE POSIBLE SUBIR EL ARCHIVO DE RESPALDO ADJUNTO.');
                return $this->redirect()->toUrl('index');
            }
            $respaldo = basename($upload['tmp_name']);
            //----------------------------------------------------------------------
        }
        $eventoOBJ->exchangeArray($form->getData());
        $eventoOBJ->setImagen($respaldo);
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
        $infoEvento = $this->DAO->getEventoDetalle($idEvento);
        $view = new ViewModel(['form' => $infoEvento]);
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
    public function actualizarimagenAction()
    {
        $idEvento = (int) $this->params()->fromQuery('idEvento', 0);
        $form = $this->getFormulario('actualizarimagen', $idEvento);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $session = $this->getInfoSesion();
                $eventoOBJ = new Evento($form->getData());
                //----------------------------------------------------------------------
                $files = $request->getFiles()->toArray();
                //----------------------------------------------------------------------
                $uploadOK = new \Laminas\Validator\File\UploadFile();
                if (!$uploadOK->isValid($files['imagen'])) {
                    $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO PRESENTA ERRORES AL CARGAR AL SERVIDOR');
                    return $this->redirect()->toUrl('index');
                }
                if (array_key_exists('imagen', $files)) {
                    $ext = pathinfo($files['imagen']['name'], PATHINFO_EXTENSION);
                    $filesize = new \Laminas\Validator\File\Size([
                        'min' => '250B',
                        'max' => '2MB',
                    ]);
                    if (!$filesize->isValid($files['imagen'])) {
                        $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO NO ESTA EN LOS LIMITES PERMITIDOS. <br> MINIMO: 250B  <br> MAXIMO: <b>2MB</b>');
                        return $this->redirect()->toUrl('index');
                    }
                    $extensiones = new \Laminas\Validator\File\Extension(array('extension' => array('jpg,jpeg,png,gif,bmp,svg')));
                    if (!$extensiones->isValid($files['imagen'])) {
                        $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO NO ES PERMITIDO. <br> ARCHIVOS PERMITIDOS: <br> txt');
                        return $this->redirect()->toUrl('index');
                    }
                    $filter = new \Laminas\Filter\File\RenameUpload([
                        'target' => $this->rutaArchivos . 'EVENTO' . '.' . $ext,
                        'randomize' => true,
                    ]);
                    //----------------------------------------------------------------------
                    $upload = $filter->filter($files['imagen']);
                    //----------------------------------------------------------------------
                    if ($upload['error'] != 0) {
                        $this->flashMessenger()->addErrorMessage('NO FUE POSIBLE SUBIR EL ARCHIVO DE RESPALDO ADJUNTO.');
                        return $this->redirect()->toUrl('index');
                    }
                    $respaldo = basename($upload['tmp_name']);
                    //----------------------------------------------------------------------
                }
                $eventoOBJ->setImagen($respaldo);
                $eventoOBJ->setModificadopor($session['login']);
                $eventoOBJ->setFechahoramod(date('Y-m-d H:i:s'));
                try {
                    $this->DAO->editar($eventoOBJ);
                    $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE EDITADO EN GESTORPORTAL');
                    return $this->redirect()->toUrl('index');
                } catch (\Exception $ex) {
                    $msgLog = "\n" . date('Y-m-d H:i:s') . " EDITAR Archivo - ArchivoController->registrar \n"
                        . $ex->getMessage()
                        . "\n----------------------------------------------------------------------- \n";
                    $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
                    fwrite($file, $msgLog);
                    fclose($file);
                    $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL ARCHIVO NO FUE EDITADO EN GESTORPORTAL.');
                }
            } else {
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE, EL ARCHIVO NO FUE EDITADO EN GESTORPORTAL');
                return $this->redirect()->toUrl('index');
            }
        }
        $view = new ViewModel([
            'form' => $form,
        ]);
        $view->setTerminal(true);
        return $view;
    }
}
