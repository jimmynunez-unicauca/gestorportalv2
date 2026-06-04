<?php

declare(strict_types=1);

namespace Administracion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Administracion\Modelo\DAO\CulturacalendarioDAO;
use Administracion\Formularios\CulturacalendarioForm;
use Administracion\Modelo\Entidades\Culturacalendario;

class CulturacalendarioController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = './public/log/';
    private $rutaArchivos = '/var/www/html/newportal/archivos/cultura/';
    /*  private $rutaArchivos = './../archivos/cultura/'; */
    //------------------------------------------------------------------------------

    public function __construct(CulturacalendarioDAO $dao)
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
    function getFormulario($action = '', $idCalendarioCultura = 0)
    {
        $form = new CulturacalendarioForm($action);
        if ($idCalendarioCultura != 0) {
            $CalendarioOBJ = $this->DAO->getCultura($idCalendarioCultura);
            $form->bind($CalendarioOBJ);
        }
        return $form;
    }
    //------------------------------------------------------------------------------
    public function indexAction()
    {
        $filtro = " calendario_cultura.estado != 'Eliminado'";
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
        $form = new CulturacalendarioForm('registrar', $fecha[0]);
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $CalendarioOBJ = new Culturacalendario();
        /* $form->setInputFilter($CalendarioOBJ->getInputFilter()); */
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
                'target' => $this->rutaArchivos . 'CULTURA' . '.' . $ext,
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
        $CalendarioOBJ->exchangeArray($form->getData());
        $CalendarioOBJ->setImagen($respaldo);
        $CalendarioOBJ->setEstado('Activo');
        $CalendarioOBJ->setRegistradopor($registradopor);
        $CalendarioOBJ->setModificadopor('');
        $CalendarioOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $CalendarioOBJ->setFechahoramod('0000-00-00 00:00:00');
        try {
            $this->DAO->registrar($CalendarioOBJ);
            $this->flashMessenger()->addSuccessMessage('EL EVENTO FUE REGISTRADO EN JIMSOFT');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR EVENTO - CulturacalendarioController->registrar \n"
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
        $idCalendarioCultura = (int) $this->params()->fromQuery('idCalendarioCultura', 0);
        $form = $this->getFormulario('editar', $idCalendarioCultura);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $session = $this->getInfoSesion();
                $CalendarioOBJ = new Culturacalendario($form->getData());
                $CalendarioOBJ->setModificadopor($session['login']);
                $CalendarioOBJ->setFechahoramod(date('Y-m-d H:i:s'));
                try {
                    $this->DAO->editar($CalendarioOBJ);
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
                $mensajesError = $form->getMessages();
                $msgLog = "\n" . date('Y-m-d H:i:s') . " FORMULARIO INVALIDO - EventoController->editar \n"
                    . print_r($mensajesError, true)
                    . "\n----------------------------------------------------------------------- \n";

                // Guardar en el log
                $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
                fwrite($file, $msgLog);
                fclose($file);

                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE, EL EVENTO NO FUE EDITADO EN JIMSOFT');
                return $this->redirect()->toUrl('index');
            }
        }
        $view = new ViewModel([
            'form' => $form,
            'idCalendarioCultura' => $idCalendarioCultura,
        ]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------  
    public function detalleAction()
    {
        $idCalendarioCultura = (int) $this->params()->fromQuery('idCalendarioCultura', 0);
        $info = $this->DAO->getCulturaDetalle($idCalendarioCultura);
        $view = new ViewModel(['form' => $info]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------
    public function eliminarAction()
    {
        $idCalendarioCultura = (int) $this->params()->fromQuery('idCalendarioCultura', 0);
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];
        $successOK = 0;
        try {
            $this->DAO->eliminar($idCalendarioCultura, $registradopor);
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
        $idCalendarioCultura = (int) $this->params()->fromQuery('idCalendarioCultura', 0);
        $start =  $this->params()->fromQuery('start', '');
        $end =  $this->params()->fromQuery('end', '');
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];
        $successOK = 0;
        try {
            $this->DAO->moverevento($idCalendarioCultura, $start, $end, $registradopor);
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
        $idCalendarioCultura = (int) $this->params()->fromQuery('idCalendarioCultura', 0);
        $form = $this->getFormulario('actualizarimagen', $idCalendarioCultura);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $session = $this->getInfoSesion();
                $CalendarioOBJ = new Culturacalendario($form->getData());
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
                        'target' => $this->rutaArchivos . 'CULTURA' . '.' . $ext,
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
                $CalendarioOBJ->setImagen($respaldo);
                $CalendarioOBJ->setModificadopor($session['login']);
                $CalendarioOBJ->setFechahoramod(date('Y-m-d H:i:s'));
                try {
                    $this->DAO->editar($CalendarioOBJ);
                    $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE EDITADO EN GESTORPORTAL');
                    return $this->redirect()->toUrl('index');
                } catch (\Exception $ex) {
                    $msgLog = "\n" . date('Y-m-d H:i:s') . " EDITAR Archivo - ArchivoController->registrar \n"
                        . $ex->getMessage()
                        . "\n----------------------------------------------------------------------- \n";
                    $file = fopen($this->rutaLog . 'GESTORPORTAL.log', 'a');
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
    //------------------------------------------------------------------------------  
}
