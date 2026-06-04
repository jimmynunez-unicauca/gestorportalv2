<?php

declare(strict_types=1);

namespace Administracion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Administracion\Modelo\DAO\CpnormativaDAO;
use Administracion\Formularios\CpnormativaForm;
use Administracion\Formularios\ProcesoForm;
use Administracion\Modelo\Entidades\Cpnormativa;

class CpnormativaController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = './public/log/';
    private $rutaArchivos = '/var/www/html/newportal/archivos/centro_posgrados/normativa/';
    /* private $rutaArchivos = './../archivos/centro_posgrados/normativa/'; */
    //------------------------------------------------------------------------------

    public function __construct(CpnormativaDAO $dao)
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
    function getFormulario($action = '', $idCPNormativa = 0)
    {
        $form = new CpnormativaForm($action);
        if ($idCPNormativa != 0) {
            $lvmenOBJ = $this->DAO->getCpnormativa($idCPNormativa);
            $form->bind($lvmenOBJ);
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
        $depenedencias = $this->DAO->getDependencias();
        $listaDepenedencias = array();
        foreach ($depenedencias  as $dep) {
            $listaDepenedencias[$dep['idDependencia']] = $dep['dependencia'];
        }
        //----------------------------------------------------------------------
        $form = new CpnormativaForm('registrar', $listaDepenedencias);
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $lvmenOBJ = new Cpnormativa();
        $form->setInputFilter($lvmenOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            print_r($form->getMessages());
            return ['form' => $form];
            exit();
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
                $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO NO ESTA EN LOS LIMITES PERMITIDOS. <br> MINIMO: 250B  <br> MAXIMO: <b>2MB</b>');
                return $this->redirect()->toUrl('index');
            }
            $extensiones = new \Laminas\Validator\File\Extension(array('extension' => array('pdf,docx,xlsx,pptx,zip')));
            if (!$extensiones->isValid($files['archivo'])) {
                $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO NO ES PERMITIDO. <br> ARCHIVOS PERMITIDOS: <br> PDF');
                return $this->redirect()->toUrl('index');
            }
            $filter = new \Laminas\Filter\File\RenameUpload([
                'target' => $this->rutaArchivos . 'CPN' . '.' . $ext,
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
        $lvmenOBJ->exchangeArray($form->getData());
        $lvmenOBJ->setArchivo($respaldo);
        $lvmenOBJ->setEstado('Activo');
        $lvmenOBJ->setRegistradopor($registradopor);
        $lvmenOBJ->setModificadopor('');
        $lvmenOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $lvmenOBJ->setFechahoramod('0000-00-00 00:00:00');
        try {
            $this->DAO->registrar($lvmenOBJ);
            $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE REGISTRADO EN JIMSOFT');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR ARCHIVO - ArchivoController->registrar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EL ARCHIVO NO FUE REGISTRADO EN JIMSOFT.');
        }
        return $this->redirect()->toUrl('index');
    }
    //------------------------------------------------------------------------------  
    public function editarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idCPNormativa = (int) $this->params()->fromQuery('idCPNormativa', 0);
            $infoCnormativa = $this->DAO->getCpnormativaDetalle($idCPNormativa);
            $depenedencias = $this->DAO->getDependencias();
            $listaDepenedencias = array();
            foreach ($depenedencias  as $dep) {
                $listaDepenedencias[$dep['idDependencia']] = $dep['dependencia'];
            }
            $form = new CpnormativaForm('editar', $listaDepenedencias);
            $form->setData($infoCnormativa);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new CpnormativaForm('editar');
        $clOBJ = new Cpnormativa();
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
    //------------------------------------------------------------------------------  
    public function detalleAction()
    {
        $idCPNormativa = (int) $this->params()->fromQuery('idCPNormativa', 0);
        $infoEmpleado = $this->DAO->getCpnormativaDetalle($idCPNormativa);
        $view = new ViewModel(['form' => $infoEmpleado]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------
    public function eliminarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idCPNormativa = (int) $this->params()->fromQuery('idCPNormativa', 0);
            $infoLven = $this->DAO->getCpnormativaDetalle($idCPNormativa);
            $depenedencias = $this->DAO->getDependencias();
            $listaDepenedencias = array();
            foreach ($depenedencias  as $dep) {
                $listaDepenedencias[$dep['idDependencia']] = $dep['dependencia'];
            }
            $form = new CpnormativaForm('eliminar', $listaDepenedencias);
            $form->setData($infoLven);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new CpnormativaForm('eliminar');
        $lvmenOBJ = new Cpnormativa();
        $form->setInputFilter($lvmenOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            //print_r($form->getMessages());
            //return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE ELIMINACION DEL ARCHIVO NO ES VALIDA');
            return $this->redirect()->toUrl('index?idCPNormativa=' . $this->params()->fromPost('idCPNormativa', 0));
        }
        //----------------------------------------------------------------------
        try {
            $lvmenOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $lvmenOBJ->setModificadopor($modificadopor);
            $lvmenOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->eliminar($lvmenOBJ);
            $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE ELIMINADA DE JIMSOFT');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ELIMINAR ARCHIVO - ContratolaboralController->eliminar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL ARCHIVO NO FUE ELIMINADA DE JIMSOFT.');
        }
        return $this->redirect()->toUrl('index?idCPNormativa=' . $lvmenOBJ->getidCPNormativa());
    }
    public function activarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idCPNormativa = (int) $this->params()->fromQuery('idCPNormativa', 0);
            $infoArchivo = $this->DAO->getCpnormativaDetalle($idCPNormativa);
            $depenedencias = $this->DAO->getDependencias();
            $listaDepenedencias = array();
            foreach ($depenedencias  as $dep) {
                $listaDepenedencias[$dep['idDependencia']] = $dep['dependencia'];
            }
            $form = new CpnormativaForm('activar', $listaDepenedencias);
            $form->setData($infoArchivo);
            $view = new ViewModel([
                'form' => $form
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new CpnormativaForm('activar');
        $lvmenOBJ = new Cpnormativa();
        $form->setInputFilter($lvmenOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            //print_r($form->getMessages());
            //return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE ELIMINACION DEL ARCHIVO NO ES VALIDA');
            return $this->redirect()->toUrl('index?idCPNormativa=' . $this->params()->fromPost('idCPNormativa', 0));
        }
        //----------------------------------------------------------------------
        try {
            $lvmenOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $lvmenOBJ->setModificadopor($modificadopor);
            $lvmenOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->activar($lvmenOBJ);
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
        return $this->redirect()->toUrl('index?idCPNormativa=' . $lvmenOBJ->getidCPNormativa());
    }
    //------------------------------------------------------------------------------  
    public function verArchivoAction()
    {
        $idCPNormativa = (int) $this->params()->fromRoute('id1', 0);
        $archivos = $this->DAO->getCpnormativaDetalle($idCPNormativa);
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
    //------------------------------------------------------------------------------
    public function actualizararchivoAction()
    {
        $idCPNormativa = (int) $this->params()->fromQuery('idCPNormativa', 0);
        $form = $this->getFormulario('actualizararchivo', $idCPNormativa);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $session = $this->getInfoSesion();
                $lvmenOBJ = new Cpnormativa($form->getData());
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
                        $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO NO ESTA EN LOS LIMITES PERMITIDOS. <br> MINIMO: 250B  <br> MAXIMO: <b>2MB</b>');
                        return $this->redirect()->toUrl('index');
                    }
                    $extensiones = new \Laminas\Validator\File\Extension(array('extension' => array('pdf,docx,xlsx,pptx,zip')));
                    if (!$extensiones->isValid($files['archivo'])) {
                        $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO NO ES PERMITIDO. <br> ARCHIVOS PERMITIDOS: <br> txt');
                        return $this->redirect()->toUrl('index');
                    }
                    $filter = new \Laminas\Filter\File\RenameUpload([
                        'target' => $this->rutaArchivos . 'CPN' . '.' . $ext,
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
                $lvmenOBJ->setarchivo($respaldo);
                $lvmenOBJ->setModificadopor($session['login']);
                $lvmenOBJ->setFechahoramod(date('Y-m-d H:i:s'));
                try {
                    $this->DAO->editar($lvmenOBJ);
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
    public function existearchivoAction()
    {
        $error = 0;
        $existe = 1;
        $archivo = $this->params()->fromQuery('archivo', '');
        if ($archivo != '') {
            $existe = $this->DAO->existeArchivo($archivo);
        } else {
            $error = 1;
        }
        return new JsonModel(array(
            'error' => $error,
            'existe' => $existe,
            'archivo' => $archivo,
        ));
    }
    //------------------------------------------------------------------------------
}
