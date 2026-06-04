<?php

declare(strict_types=1);

namespace Administracion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Administracion\Modelo\DAO\ArchivoDAO;
use Administracion\Formularios\ArchivoForm;
use Administracion\Modelo\Entidades\Archivo;

class ArchivoController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = './public/log/';
    private $rutaArchivos = '/var/www/html/newportal/archivos/publicos/';
    //private $rutaArchivos = './../archivos/publicos/';
    //------------------------------------------------------------------------------

    public function __construct(ArchivoDAO $dao)
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
    function getFormulario($action = '', $idArchivo = 0)
    {
        $form = new ArchivoForm($action);
        if ($idArchivo != 0) {
            $archivoOBJ = $this->DAO->getArchivo($idArchivo);
            $form->bind($archivoOBJ);
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
    public function indexenAction()
    {
        $filtro = "";
        return new ViewModel([
            'fetchAll' => $this->DAO->fetchAllEn($filtro),
        ]);
    }

    //------------------------------------------------------------------------------
    public function registrarAction()
    {
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];
        $dependencias = $this->DAO->getDependencias();
        //----------------------------------------------------------------------
        $form = new ArchivoForm('registrar');
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel([
                'form' => $form,
                'dependencias' => $dependencias,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $archivoOBJ = new Archivo();
        $form->setInputFilter($archivoOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            /* print_r($form->getMessages());
            return ['form' => $form]; */
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL ARCHIVO NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        //----------------------------------------------------------------------
        $files = $request->getFiles()->toArray();
        //----------------------------------------------------------------------
        $uploadOK = new \Laminas\Validator\File\UploadFile();
        if (!$uploadOK->isValid($files['archivo'])) {
            $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO PRESENTA ERRORES AL CARGAR AL SERVIDOR');
            return $this->redirect()->toUrl('index');
        }
        if (array_key_exists('archivo', $files)) {
            $ext = pathinfo($files['archivo']['name'], PATHINFO_EXTENSION);
            $filesize = new \Laminas\Validator\File\Size([
                'min' => '250B',
                'max' => '25MB',
            ]);
            if (!$filesize->isValid($files['archivo'])) {
                $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO NO ESTA EN LOS LIMITES PERMITIDOS. <br> MINIMO: 250B  <br> MAXIMO: <b>25MB</b>');
                return $this->redirect()->toUrl('index');
            }
            $extensiones = new \Laminas\Validator\File\Extension(array('extension' => array('pdf,docx,xlsx,pptx')));
            if (!$extensiones->isValid($files['archivo'])) {
                $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO NO ES PERMITIDO. <br> ARCHIVOS PERMITIDOS: <br> PDF');
                return $this->redirect()->toUrl('index');
            }
            $filter = new \Laminas\Filter\File\RenameUpload([
                'target' => $this->rutaArchivos . 'DOC' . '.' . $ext,
                'randomize' => true,
            ]);
            //----------------------------------------------------------------------
            $upload = $filter->filter($files['archivo']);
            //----------------------------------------------------------------------
            if ($upload['error'] != 0) {
                $this->flashMessenger()->addErrorMessage('NO FUE POSIBLE SUBIR EL ARCHIVO DE RESPALDO ADJUNTO.');
                return $this->redirect()->toUrl('index');
            }
            $respaldo = basename($upload['tmp_name']);
            //----------------------------------------------------------------------
        }

        $archivoOBJ->exchangeArray($form->getData());
        $depen = $this->params()->fromPost('dependencias', []);
        $archivoOBJ->setArchivo($respaldo);
        $archivoOBJ->setEstado('Activo');
        $archivoOBJ->setRegistradopor($registradopor);
        $archivoOBJ->setModificadopor('');
        $archivoOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $archivoOBJ->setFechahoramod('0000-00-00 00:00:00');
        try {
            $this->DAO->registrar($archivoOBJ, $depen);
            $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE REGISTRADO EN GESTORPORTAL');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR ARCHIVO - ArchivoController->registrar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EL ARCHIVO NO FUE REGISTRADO EN GESTORPORTAL.');
        }
        return $this->redirect()->toUrl('index');
    }
    //------------------------------------------------------------------------------  
    public function editarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idArchivo = (int) $this->params()->fromQuery('idArchivo', 0);
            $infoArchivo = $this->DAO->getArchivoDetalle($idArchivo);
            $depeSelect = $this->DAO->getArchivoDependencia($idArchivo);
            $form = new ArchivoForm('editar');
            $form->setData($infoArchivo);
            $view = new ViewModel([
                'form' => $form,
                'dependencias' => $depeSelect,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new ArchivoForm('editar');
        $clOBJ = new Archivo();
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
            $this->flashMessenger()->addSuccessMessage('LA INFORMACION DEL ARCHIVO FUE ACTUALIZADA');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ACTUALIZAR CONTRATO LABORAL - ArchivoController->editar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE!.');
        }
        return $this->redirect()->toUrl('index');
    }
    public function editarenAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idArchivo = (int) $this->params()->fromQuery('idArchivo', 0);
            $infoArchivo = $this->DAO->getArchivoDetalleEn($idArchivo);
            $depeSelect = $this->DAO->getArchivoDependencia($idArchivo);
            $form = new ArchivoForm('editaren');
            $form->setData($infoArchivo);
            $view = new ViewModel([
                'form' => $form,
                'dependencias' => $depeSelect,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new ArchivoForm('editaren');
        $clOBJ = new Archivo();
        $form->setInputFilter($clOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            /*  print_r($form->getMessages());
            return ['form' => $form]; */
            $this->flashMessenger()->addErrorMessage('LA INFORMACION A GUARDAR NO ES VALIDA');
            return $this->redirect()->toUrl('indexen');
        }
        //----------------------------------------------------------------------
        try {
            $clOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $clOBJ->setModificadopor($modificadopor);
            $clOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $clOBJ->setEstado('Activo');
            $this->DAO->editarEn($clOBJ);
            $this->flashMessenger()->addSuccessMessage('LA INFORMACION DEL ARCHIVO FUE ACTUALIZADA');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ACTUALIZAR CONTRATO LABORAL - ArchivoController->editar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE!.');
        }
        return $this->redirect()->toUrl('indexen');
    }
    //------------------------------------------------------------------------------  
    public function detalleAction()
    {
        $idArchivo = (int) $this->params()->fromQuery('idArchivo', 0);
        $dependencias = $this->DAO->getArchivoDependencia($idArchivo);
        $infoEmpleado = $this->DAO->getArchivoDetalle($idArchivo);
        $view = new ViewModel(['form' => $infoEmpleado, 'dependencias' => $dependencias]);
        $view->setTerminal(true);
        return $view;
    }
    public function detalleenAction()
    {
        $idArchivo = (int) $this->params()->fromQuery('idArchivo', 0);
        $dependencias = $this->DAO->getArchivoDependencia($idArchivo);
        $infoEmpleado = $this->DAO->getArchivoDetalleEn($idArchivo);
        $view = new ViewModel(['form' => $infoEmpleado, 'dependencias' => $dependencias]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------
    public function eliminarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idArchivo = (int) $this->params()->fromQuery('idArchivo', 0);
            $infoArchivo = $this->DAO->getArchivoDetalle($idArchivo);
            $dependencias = $this->DAO->getDependencias();
            $listaDepenedencias = array();
            foreach ($dependencias as $dep) {
                $listaDepenedencias[$dep['idDependencia']] = $dep['dependencia'];
            }
            $form = new ArchivoForm('eliminar', $listaDepenedencias);
            $form->setData($infoArchivo);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new ArchivoForm('eliminar');
        $archivoOBJ = new Archivo();
        $form->setInputFilter($archivoOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            //print_r($form->getMessages());
            //return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE ELIMINACION DEL ARCHIVO NO ES VALIDA');
            return $this->redirect()->toUrl('index?idArchivo=' . $this->params()->fromPost('idArchivo', 0));
        }
        //----------------------------------------------------------------------
        try {
            $archivoOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $archivoOBJ->setModificadopor($modificadopor);
            $archivoOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->eliminar($archivoOBJ);
            $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE ELIMINADA DE GESTORPORTAL');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ELIMINAR ARCHIVO - ArchivoController->eliminar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL ARCHIVO NO FUE ELIMINADA DE GESTORPORTAL.');
        }
        return $this->redirect()->toUrl('index?idArchivo=' . $archivoOBJ->getidArchivo());
    }
    public function eliminarenAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idArchivo = (int) $this->params()->fromQuery('idArchivo', 0);
            $infoArchivo = $this->DAO->getArchivoDetalleEn($idArchivo);
            $dependencias = $this->DAO->getDependencias();
            $listaDepenedencias = array();
            foreach ($dependencias as $dep) {
                $listaDepenedencias[$dep['idDependencia']] = $dep['dependencia'];
            }
            $form = new ArchivoForm('eliminaren', $listaDepenedencias);
            $form->setData($infoArchivo);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new ArchivoForm('eliminaren');
        $archivoOBJ = new Archivo();
        $form->setInputFilter($archivoOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            //print_r($form->getMessages());
            //return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE ELIMINACION DEL ARCHIVO NO ES VALIDA');
            return $this->redirect()->toUrl('index?idArchivo=' . $this->params()->fromPost('idArchivo', 0));
        }
        //----------------------------------------------------------------------
        try {
            $archivoOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $archivoOBJ->setModificadopor($modificadopor);
            $archivoOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->eliminarEn($archivoOBJ);
            $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE ELIMINADA DE GESTORPORTAL');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ELIMINAR ARCHIVO - ArchivoController->eliminaren \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL ARCHIVO NO FUE ELIMINADA DE GESTORPORTAL.');
        }
        return $this->redirect()->toUrl('indexen?idArchivo=' . $archivoOBJ->getidArchivo());
    }
    public function activarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idArchivo = (int) $this->params()->fromQuery('idArchivo', 0);
            $infoArchivo = $this->DAO->getArchivoDetalle($idArchivo);
            $dependencias = $this->DAO->getDependencias();
            $listaDepenedencias = array();
            foreach ($dependencias as $dep) {
                $listaDepenedencias[$dep['idDependencia']] = $dep['dependencia'];
            }
            $form = new ArchivoForm('activar', $listaDepenedencias);
            $form->setData($infoArchivo);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new ArchivoForm('activar');
        $archivoOBJ = new Archivo();
        $form->setInputFilter($archivoOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            //print_r($form->getMessages());
            //return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE ELIMINACION DEL ARCHIVO NO ES VALIDA');
            return $this->redirect()->toUrl('index?idArchivo=' . $this->params()->fromPost('idArchivo', 0));
        }
        //----------------------------------------------------------------------
        try {
            $archivoOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $archivoOBJ->setModificadopor($modificadopor);
            $archivoOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->activar($archivoOBJ);
            $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE RECUPERADO DE GESTORPORTAL');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ELIMINAR ARCHIVO - ArchivoController->eliminar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL ARCHIVO NO FUE ELIMINADA DE GESTORPORTAL.');
        }
        return $this->redirect()->toUrl('index?idArchivo=' . $archivoOBJ->getidArchivo());
    }
    public function activarenAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idArchivo = (int) $this->params()->fromQuery('idArchivo', 0);
            $infoArchivo = $this->DAO->getArchivoDetalleEn($idArchivo);
            $dependencias = $this->DAO->getDependencias();
            $listaDepenedencias = array();
            foreach ($dependencias as $dep) {
                $listaDepenedencias[$dep['idDependencia']] = $dep['dependencia'];
            }
            $form = new ArchivoForm('activaren', $listaDepenedencias);
            $form->setData($infoArchivo);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new ArchivoForm('activaren');
        $archivoOBJ = new Archivo();
        $form->setInputFilter($archivoOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            //print_r($form->getMessages());
            //return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE ELIMINACION DEL ARCHIVO NO ES VALIDA');
            return $this->redirect()->toUrl('index?idArchivo=' . $this->params()->fromPost('idArchivo', 0));
        }
        //----------------------------------------------------------------------
        try {
            $archivoOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $archivoOBJ->setModificadopor($modificadopor);
            $archivoOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->activarEn($archivoOBJ);
            $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE RECUPERADO DE GESTORPORTAL');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ELIMINAR ARCHIVO - ArchivoController->eliminar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL ARCHIVO NO FUE ELIMINADA DE GESTORPORTAL.');
        }
        return $this->redirect()->toUrl('indexen?idArchivo=' . $archivoOBJ->getidArchivo());
    }
    //------------------------------------------------------------------------------  
    public function verArchivoAction()
    {
        $idArchivo = (int) $this->params()->fromRoute('id1', 0);
        $archivos = $this->DAO->getArchivoDetalle($idArchivo);
        $archivo = $archivos['archivo'];
        if ($archivo == '') {
            $this->flashMessenger()->addErrorMessage('NO FUE POSIBLE OBTENER EL ARCHIVO DESDE LA BASE DE DATOS');
            return $this->redirect()->toUrl('index');
        }
        $archivo = $this->rutaArchivos . '/' . $archivo;
        if (is_file($archivo)) {
            $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
            switch ($ext) {
                case 'pdf':
                    header('Content-Type: application/pdf');
                    break;
                case 'jpg':
                    header('Content-Type: image/jpeg');
                    break;
                case 'png':
                    header('Content-Type: image/jpeg');
                    break;
                case 'jpeg':
                    header('Content-Type: image/jpeg');
                    break;
                case 'rar':
                    header("Content-type: application/octet-stream");
                    header("Content-disposition: attachment; filename=archivoRespaldo.rar");
                    break;
                default:
                    header("Content-type: application/octet-stream");
                    header("Content-disposition: attachment; filename=archivoRespaldo." . $ext);
                    break;
            }
            readfile($archivo);
        } else {
            $this->flashMessenger()->addErrorMessage('NO FUE POSIBLE ENCONTRAR EL ARCHIVO EN EL SERVIDOR');
            return $this->redirect()->toUrl('../../administracion/index');
        }
    }
    public function verArchivoenAction()
    {
        $idArchivo = (int) $this->params()->fromRoute('id1', 0);
        $archivos = $this->DAO->getArchivoDetalleEn($idArchivo);
        $archivo = $archivos['archivo'];
        if ($archivo == '') {
            $this->flashMessenger()->addErrorMessage('NO FUE POSIBLE OBTENER EL ARCHIVO DESDE LA BASE DE DATOS');
            return $this->redirect()->toUrl('indexen');
        }
        $archivo = $this->rutaArchivos . '/' . $archivo;
        if (is_file($archivo)) {
            $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
            switch ($ext) {
                case 'pdf':
                    header('Content-Type: application/pdf');
                    break;
                case 'jpg':
                    header('Content-Type: image/jpeg');
                    break;
                case 'png':
                    header('Content-Type: image/jpeg');
                    break;
                case 'jpeg':
                    header('Content-Type: image/jpeg');
                    break;
                case 'rar':
                    header("Content-type: application/octet-stream");
                    header("Content-disposition: attachment; filename=archivoRespaldo.rar");
                    break;
                default:
                    header("Content-type: application/octet-stream");
                    header("Content-disposition: attachment; filename=archivoRespaldo." . $ext);
                    break;
            }
            readfile($archivo);
        } else {
            $this->flashMessenger()->addErrorMessage('NO FUE POSIBLE ENCONTRAR EL ARCHIVO EN EL SERVIDOR');
            return $this->redirect()->toUrl('../../administracion/index');
        }
    }
    //------------------------------------------------------------------------------ 
    public function actualizararchivoAction()
    {
        $idArchivo = (int) $this->params()->fromQuery('idArchivo', 0);
        $form = $this->getFormulario('actualizararchivo', $idArchivo);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $session = $this->getInfoSesion();
                $archivoOBJ = new Archivo($form->getData());
                //----------------------------------------------------------------------
                $files = $request->getFiles()->toArray();
                //----------------------------------------------------------------------
                $uploadOK = new \Laminas\Validator\File\UploadFile();
                if (!$uploadOK->isValid($files['archivo'])) {
                    $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO PRESENTA ERRORES AL CARGAR AL SERVIDOR');
                    return $this->redirect()->toUrl('index');
                }
                if (array_key_exists('archivo', $files)) {
                    $ext = pathinfo($files['archivo']['name'], PATHINFO_EXTENSION);
                    $filesize = new \Laminas\Validator\File\Size([
                        'min' => '250B',
                        'max' => '2MB',
                    ]);
                    if (!$filesize->isValid($files['archivo'])) {
                        $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO NO ESTA EN LOS LIMITES PERMITIDOS. <br> MINIMO: 250B  <br> MAXIMO: <b>2MB</b>');
                        return $this->redirect()->toUrl('index');
                    }
                    $extensiones = new \Laminas\Validator\File\Extension(array('extension' => array('pdf,docx,xlsx,pptx')));
                    if (!$extensiones->isValid($files['archivo'])) {
                        $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO NO ES PERMITIDO. <br> ARCHIVOS PERMITIDOS: <br> txt');
                        return $this->redirect()->toUrl('index');
                    }
                    $filter = new \Laminas\Filter\File\RenameUpload([
                        'target' => $this->rutaArchivos . 'DOC' . '.' . $ext,
                        'randomize' => true,
                    ]);
                    //----------------------------------------------------------------------
                    $upload = $filter->filter($files['archivo']);
                    //----------------------------------------------------------------------
                    if ($upload['error'] != 0) {
                        $this->flashMessenger()->addErrorMessage('NO FUE POSIBLE SUBIR EL ARCHIVO DE RESPALDO ADJUNTO.');
                        return $this->redirect()->toUrl('index');
                    }
                    $respaldo = basename($upload['tmp_name']);
                    //----------------------------------------------------------------------
                }
                $archivoOBJ->setarchivo($respaldo);
                $archivoOBJ->setModificadopor($session['login']);
                $archivoOBJ->setFechahoramod(date('Y-m-d H:i:s'));
                try {
                    $this->DAO->editar($archivoOBJ);
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
    public function actualizararchivoenAction()
    {
        $idArchivo = (int) $this->params()->fromQuery('idArchivo', 0);
        $form = $this->getFormulario('actualizararchivoen', $idArchivo);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $session = $this->getInfoSesion();
                $archivoOBJ = new Archivo($form->getData());
                //----------------------------------------------------------------------
                $files = $request->getFiles()->toArray();
                //----------------------------------------------------------------------
                $uploadOK = new \Laminas\Validator\File\UploadFile();
                if (!$uploadOK->isValid($files['archivo'])) {
                    $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO PRESENTA ERRORES AL CARGAR AL SERVIDOR');
                    return $this->redirect()->toUrl('indexen');
                }
                if (array_key_exists('archivo', $files)) {
                    $ext = pathinfo($files['archivo']['name'], PATHINFO_EXTENSION);
                    $filesize = new \Laminas\Validator\File\Size([
                        'min' => '250B',
                        'max' => '2MB',
                    ]);
                    if (!$filesize->isValid($files['archivo'])) {
                        $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO NO ESTA EN LOS LIMITES PERMITIDOS. <br> MINIMO: 250B  <br> MAXIMO: <b>2MB</b>');
                        return $this->redirect()->toUrl('indexen');
                    }
                    $extensiones = new \Laminas\Validator\File\Extension(array('extension' => array('pdf,docx,xlsx,pptx')));
                    if (!$extensiones->isValid($files['archivo'])) {
                        $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO NO ES PERMITIDO. <br> ARCHIVOS PERMITIDOS: <br> txt');
                        return $this->redirect()->toUrl('indexen');
                    }
                    $filter = new \Laminas\Filter\File\RenameUpload([
                        'target' => $this->rutaArchivos . 'DOC' . '.' . $ext,
                        'randomize' => true,
                    ]);
                    //----------------------------------------------------------------------
                    $upload = $filter->filter($files['archivo']);
                    //----------------------------------------------------------------------
                    if ($upload['error'] != 0) {
                        $this->flashMessenger()->addErrorMessage('NO FUE POSIBLE SUBIR EL ARCHIVO DE RESPALDO ADJUNTO.');
                        return $this->redirect()->toUrl('indexen');
                    }
                    $respaldo = basename($upload['tmp_name']);
                    //----------------------------------------------------------------------
                }
                $archivoOBJ->setarchivo($respaldo);
                $archivoOBJ->setModificadopor($session['login']);
                $archivoOBJ->setFechahoramod(date('Y-m-d H:i:s'));
                try {
                    $this->DAO->editarEn($archivoOBJ);
                    $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE EDITADO EN GESTORPORTAL');
                    return $this->redirect()->toUrl('indexen');
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
                return $this->redirect()->toUrl('indexen');
            }
        }
        $view = new ViewModel([
            'form' => $form,
        ]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------   
    public function eliminarDepAction()
    {
        $idArchivo = (int) $this->params()->fromQuery('idArchivo', 0);
        $idDependencia = (int) $this->params()->fromQuery('idDependencia', 0);
        if ($idArchivo != 0 && $idDependencia != 0) {
            try {
                $this->DAO->eliminarDepe($idArchivo, $idDependencia);
                $this->flashMessenger()->addSuccessMessage('LA DEPENDENCIA FUE ELIMINADA CON EXITO');
            } catch (\Exception $ex) {
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN ERROR ' . $ex);
            }
        }
        return new JsonModel([]);
    }
    //------------------------------------------------------------------------------ 
    public function agregardepeAction()
    {
        $idArchivo = (int) $this->params()->fromQuery('idArchivo', 0);
        $dependencias = $this->DAO->getDependenciasArch($idArchivo);
        $view = new ViewModel([
            'dependencias' => $dependencias,
        ]);
        $view->setTerminal(true);
        return $view;
    }
    public function addDepAction()
    {
        $idArchivo = (int) $this->params()->fromQuery('idArchivo', 0);
        $idDependencia = (int) $this->params()->fromQuery('idDependencia', 0);
        if ($idArchivo != 0 && $idDependencia != 0) {
            try {
                $this->DAO->agregarDepe($idArchivo, $idDependencia);
                $this->flashMessenger()->addSuccessMessage('LA DEPENDENCIA FUE AGREGADA CON EXITO');
            } catch (\Exception $ex) {
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN ERROR ' . $ex);
            }
        }
        return new JsonModel([]);
    }
    //------------------------------------------------------------------------------   
}
