<?php

declare(strict_types=1);

namespace Documentos\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Documentos\Modelo\DAO\ArchivohistoricoDAO;
use Documentos\Formularios\ArchivohistoricoForm;
use Documentos\Modelo\Entidades\Archivohistorico;

class ArchivohistoricoController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = './public/log/';
    private $rutaArchivos = '/var/www/html/newportal/archivos/archivo_historico/';
    //private $rutaArchivos = './../archivos/publicos/';
    //------------------------------------------------------------------------------

    public function __construct(ArchivohistoricoDAO $dao)
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
    function getFormulario($action = '', $id = 0)
    {
        $form = new ArchivohistoricoForm($action);
        if ($id != 0) {
            $ahOBJ = $this->DAO->getArchivohistorico($id);
            $form->bind($ahOBJ);
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
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];
        //----------------------------------------------------------------------
        $form = new ArchivohistoricoForm('registrar');
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $ahOBJ = new Archivohistorico();
        $form->setInputFilter($ahOBJ->getInputFilter());
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
                'target' => $this->rutaArchivos . 'AH' . '.' . $ext,
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

        $ahOBJ->exchangeArray($form->getData());
        $ahOBJ->setArchivo($respaldo);
        $ahOBJ->setEstado('Activo');
        $ahOBJ->setRegistradopor($registradopor);
        $ahOBJ->setModificadopor('');
        $ahOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $ahOBJ->setFechahoramod('0000-00-00 00:00:00');
        try {
            $this->DAO->registrar($ahOBJ);
            $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE REGISTRADO EN GESTORPORTAL');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR ARCHIVO - ArchivohistoricoController->registrar \n"
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
            $id = (int) $this->params()->fromQuery('id', 0);
            $infoArchivo = $this->DAO->getArchivoDetalle($id);
            $form = new ArchivohistoricoForm('editar');
            $form->setData($infoArchivo);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new ArchivohistoricoForm('editar');
        $clOBJ = new Archivohistorico();
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
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ACTUALIZAR CONTRATO LABORAL - ArchivohistoricoController->editar \n"
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
        $id = (int) $this->params()->fromQuery('id', 0);
        $infoEmpleado = $this->DAO->getArchivoDetalle($id);
        $view = new ViewModel(['form' => $infoEmpleado]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------
    public function eliminarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);
            $infoArchivo = $this->DAO->getArchivoDetalle($id);
            $form = new ArchivohistoricoForm('eliminar');
            $form->setData($infoArchivo);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new ArchivohistoricoForm('eliminar');
        $ahOBJ = new Archivohistorico();
        $form->setInputFilter($ahOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            //print_r($form->getMessages());
            //return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE ELIMINACION DEL ARCHIVO NO ES VALIDA');
            return $this->redirect()->toUrl('index?id=' . $this->params()->fromPost('id', 0));
        }
        //----------------------------------------------------------------------
        try {
            $ahOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $ahOBJ->setModificadopor($modificadopor);
            $ahOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->eliminar($ahOBJ);
            $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE ELIMINADA DE GESTORPORTAL');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ELIMINAR ARCHIVO - ArchivohistoricoController->eliminar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL ARCHIVO NO FUE ELIMINADA DE GESTORPORTAL.');
        }
        return $this->redirect()->toUrl('index?id=' . $ahOBJ->getIdarchivo_historico());
    }
    //------------------------------------------------------------------------------   
    public function activarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);
            $infoArchivo = $this->DAO->getArchivoDetalle($id);

            $form = new ArchivohistoricoForm('activar');
            $form->setData($infoArchivo);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new ArchivohistoricoForm('activar');
        $ahOBJ = new Archivohistorico();
        $form->setInputFilter($ahOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            //print_r($form->getMessages());
            //return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE ELIMINACION DEL ARCHIVO NO ES VALIDA');
            return $this->redirect()->toUrl('index?id=' . $this->params()->fromPost('id', 0));
        }
        //----------------------------------------------------------------------
        try {
            $ahOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $ahOBJ->setModificadopor($modificadopor);
            $ahOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->activar($ahOBJ);
            $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE RECUPERADO DE GESTORPORTAL');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ELIMINAR ARCHIVO - ArchivohistoricoController->eliminar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL ARCHIVO NO FUE ELIMINADA DE GESTORPORTAL.');
        }
        return $this->redirect()->toUrl('index?id=' . $ahOBJ->getIdarchivo_historico());
    }
    //------------------------------------------------------------------------------  
    public function verArchivoAction()
    {
        $id = (int) $this->params()->fromRoute('id1', 0);
        $archivos = $this->DAO->getArchivoDetalle($id);
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
            return $this->redirect()->toUrl('../../Documentos/index');
        }
    }
    //------------------------------------------------------------------------------ 
    public function actualizararchivoAction()
    {
        $id = (int) $this->params()->fromQuery('id', 0);
        $form = $this->getFormulario('actualizararchivo', $id);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $session = $this->getInfoSesion();
                $ahOBJ = new Archivohistorico($form->getData());
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
                        'target' => $this->rutaArchivos . 'AH' . '.' . $ext,
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
                $ahOBJ->setarchivo($respaldo);
                $ahOBJ->setModificadopor($session['login']);
                $ahOBJ->setFechahoramod(date('Y-m-d H:i:s'));
                try {
                    $this->DAO->editar($ahOBJ);
                    $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE EDITADO EN GESTORPORTAL');
                    return $this->redirect()->toUrl('index');
                } catch (\Exception $ex) {
                    $msgLog = "\n" . date('Y-m-d H:i:s') . " EDITAR Archivo - ArchivohistoricoController->registrar \n"
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
