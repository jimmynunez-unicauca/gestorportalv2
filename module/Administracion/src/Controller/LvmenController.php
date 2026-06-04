<?php

declare(strict_types=1);

namespace Administracion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Administracion\Modelo\DAO\LvmenDAO;
use Administracion\Formularios\LvmenForm;
use Administracion\Formularios\ProcesoForm;
use Administracion\Formularios\BpmnForm;
use Administracion\Modelo\Entidades\Lvmen;
use Administracion\Modelo\Entidades\Bpmn;

class LvmenController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = './public/log/';
    private $rutaArchivos = '/var/www/html/newportal/archivos/lumen/';
    /* private $rutaArchivos = './../archivos/lumen/'; */
    //------------------------------------------------------------------------------

    public function __construct(LvmenDAO $dao)
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
    function getFormulario($action = '', $idLvmen = 0)
    {
        $form = new LvmenForm($action);
        if ($idLvmen != 0) {
            $lvmenOBJ = $this->DAO->getLvmen($idLvmen);
            $form->bind($lvmenOBJ);
        }
        return $form;
    }
    function getFormularioProceso($idSubproceso = 0)
    {
        $procesos = $this->DAO->getProcesos();
        $procesoSelect = array();
        foreach ($procesos as $dpto) {
            $procesoSelect[$dpto['idProceso']] = $dpto['proceso'];
        }
        if ($idSubproceso != 0) {
            $procesoOBJ = $this->DAO->getSubProcesoOBJ($idSubproceso);
            $tipos = $this->DAO->getTipoProceso($procesoOBJ->getIdProceso());
            $subProcesos = $this->DAO->getSubproceso($procesoOBJ->getIdTipoProceso());
            $tipoSelect = array();
            $subProcesoSelect = array();
            foreach ($tipos as $tipo) {
                $tipoSelect[$tipo['idTipoProceso']] = $tipo['tipoProceso'];
            }
            foreach ($subProcesos as $subProceso) {
                $subProcesoSelect[$subProceso['idSubproceso']] = $subProceso['subproceso'];
            }
            $form = new ProcesoForm($procesoSelect, $tipoSelect, $subProcesoSelect);
            $form->bind($procesoOBJ);
        } else {
            $form = new ProcesoForm($procesoSelect);
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
        $Procesos = $this->DAO->getProcesos();
        $listaProceso = array();
        foreach ($Procesos  as $pro) {
            $listaProceso[$pro['idProceso']] = $pro['proceso'];
        }
        $depenedencias = $this->DAO->getDependencias();
        $listaDepenedencias = array();
        foreach ($depenedencias  as $dep) {
            $listaDepenedencias[$dep['idDependencia']] = $dep['dependencia'];
        }
        //----------------------------------------------------------------------
        $form = new LvmenForm('registrar', $listaProceso, $listaDepenedencias);
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $lvmenOBJ = new Lvmen();
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
            $extensiones = new \Laminas\Validator\File\Extension(array('extension' => array('pdf,docx,xlsx,pptx')));
            if (!$extensiones->isValid($files['archivo'])) {
                $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO NO ES PERMITIDO. <br> ARCHIVOS PERMITIDOS: <br> PDF');
                return $this->redirect()->toUrl('index');
            }
            $filter = new \Laminas\Filter\File\RenameUpload([
                'target' => $this->rutaArchivos . $files['archivo']['name'],
                'randomize' => false,
                'overwrite' => true,
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
            $idLvmen = (int) $this->params()->fromQuery('idLvmen', 0);
            $infoLvmen = $this->DAO->getLvmenDetalle($idLvmen);
            $Procesos = $this->DAO->getProcesos();
            $listaProceso = array();
            foreach ($Procesos  as $pro) {
                $listaProceso[$pro['idProceso']] = $pro['proceso'];
            }
            $depenedencias = $this->DAO->getDependencias();
            $listaDepenedencias = array();
            foreach ($depenedencias  as $dep) {
                $listaDepenedencias[$dep['idDependencia']] = $dep['dependencia'];
            }
            $form = new LvmenForm('editar', $listaProceso, $listaDepenedencias);
            $form->setData($infoLvmen);
            $formProceso = $this->getFormularioProceso($form->get("idSubproceso")->getValue());
            $view = new ViewModel([
                'form' => $form,
                'formProceso' => $formProceso
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new LvmenForm('editar');
        $clOBJ = new Lvmen();
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
        $idLvmen = (int) $this->params()->fromQuery('idLvmen', 0);
        $infoEmpleado = $this->DAO->getLvmenDetalle($idLvmen);
        $view = new ViewModel(['form' => $infoEmpleado]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------
    public function eliminarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idLvmen = (int) $this->params()->fromQuery('idLvmen', 0);
            $infoLven = $this->DAO->getLvmenDetalle($idLvmen);
            $Procesos = $this->DAO->getProcesos();
            $listaProceso = array();
            foreach ($Procesos  as $pro) {
                $listaProceso[$pro['idProceso']] = $pro['proceso'];
            }
            $depenedencias = $this->DAO->getDependencias();
            $listaDepenedencias = array();
            foreach ($depenedencias  as $dep) {
                $listaDepenedencias[$dep['idDependencia']] = $dep['dependencia'];
            }
            $form = new LvmenForm('eliminar', $listaProceso, $listaDepenedencias);
            $form->setData($infoLven);
            $formProceso = $this->getFormularioProceso($form->get("idSubproceso")->getValue());
            $view = new ViewModel([
                'form' => $form,
                'formProceso' => $formProceso
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new LvmenForm('eliminar');
        $lvmenOBJ = new Lvmen();
        $form->setInputFilter($lvmenOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            //print_r($form->getMessages());
            //return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE ELIMINACION DEL ARCHIVO NO ES VALIDA');
            return $this->redirect()->toUrl('index?idLvmen=' . $this->params()->fromPost('idLvmen', 0));
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
        return $this->redirect()->toUrl('index?idLvmen=' . $lvmenOBJ->getIdLvmen());
    }
    public function activarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idLvmen = (int) $this->params()->fromQuery('idLvmen', 0);
            $infoArchivo = $this->DAO->getLvmenDetalle($idLvmen);
            $Procesos = $this->DAO->getProcesos();
            $listaProceso = array();
            foreach ($Procesos  as $pro) {
                $listaProceso[$pro['idProceso']] = $pro['proceso'];
            }
            $depenedencias = $this->DAO->getDependencias();
            $listaDepenedencias = array();
            foreach ($depenedencias  as $dep) {
                $listaDepenedencias[$dep['idDependencia']] = $dep['dependencia'];
            }
            $form = new LvmenForm('activar', $listaProceso, $listaDepenedencias);
            $form->setData($infoArchivo);
            $formProceso = $this->getFormularioProceso($form->get("idSubproceso")->getValue());
            $view = new ViewModel([
                'form' => $form,
                'formProceso' => $formProceso,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new LvmenForm('activar');
        $lvmenOBJ = new Lvmen();
        $form->setInputFilter($lvmenOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            //print_r($form->getMessages());
            //return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE ELIMINACION DEL ARCHIVO NO ES VALIDA');
            return $this->redirect()->toUrl('index?idLvmen=' . $this->params()->fromPost('idLvmen', 0));
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
        return $this->redirect()->toUrl('index?idLvmen=' . $lvmenOBJ->getidLvmen());
    }
    //------------------------------------------------------------------------------  
    public function getSelectTipoProcesosAction()
    {
        $idProceso = (int) $this->params()->fromQuery('idProceso', 0);
        $view = new ViewModel(array(
            'tipoProcesos' => $this->DAO->getTipoProceso($idProceso),
        ));
        $view->setTerminal(true);
        return $view;
    }
    public function getSelectSubprocesosAction()
    {
        $idTipoProceso = (int) $this->params()->fromQuery('idTipoProceso', 0);
        $view = new ViewModel(array(
            'subprocesos' => $this->DAO->getSubproceso($idTipoProceso),
        ));
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------ 
    public function verArchivoAction()
    {
        $idLvmen = (int) $this->params()->fromRoute('id1', 0);
        $archivos = $this->DAO->getLvmenDetalle($idLvmen);
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
        $idLvmen = (int) $this->params()->fromQuery('idLvmen', 0);
        $form = $this->getFormulario('actualizararchivo', $idLvmen);
        $formProceso = $this->getFormularioProceso($form->get("idSubproceso")->getValue());
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $session = $this->getInfoSesion();
                $lvmenOBJ = new Lvmen($form->getData());
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
                    $extensiones = new \Laminas\Validator\File\Extension(array('extension' => array('pdf,docx,xlsx,pptx')));
                    if (!$extensiones->isValid($files['archivo'])) {
                        $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO NO ES PERMITIDO. <br> ARCHIVOS PERMITIDOS: <br> txt');
                        return $this->redirect()->toUrl('index');
                    }
                    $filter = new \Laminas\Filter\File\RenameUpload([
                        'target' => $this->rutaArchivos . $files['archivo']['name'],
                        'randomize' => false,
                        'overwrite' => true,
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
            'formProceso' => $formProceso,
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
    public function subirppmnAction()
    {
        $idLvmen = (int) $this->params()->fromQuery('idLvmen', 0);
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];
        //----------------------------------------------------------------------
        $form = new BpmnForm('subirppmn');
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel([
                'form' => $form,
                'idLvmen' => $idLvmen,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $bpmnOBJ = new Bpmn();
        $form->setInputFilter($bpmnOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            print_r($form->getMessages());
            return ['form' => $form];
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
            $extensiones = new \Laminas\Validator\File\Extension(array('extension' => array('pdf,docx,xlsx,pptx')));
            if (!$extensiones->isValid($files['archivo'])) {
                $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO NO ES PERMITIDO. <br> ARCHIVOS PERMITIDOS: <br> PDF');
                return $this->redirect()->toUrl('index');
            }
            $filter = new \Laminas\Filter\File\RenameUpload([
                'target' => $this->rutaArchivos . $files['archivo']['name'],
                'randomize' => false,
                'overwrite' => true,
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
        $bpmnOBJ->exchangeArray($form->getData());
        $bpmnOBJ->setArchivo($respaldo);
        $bpmnOBJ->setEstado('Activo');
        $bpmnOBJ->setRegistradopor($registradopor);
        $bpmnOBJ->setModificadopor('');
        $bpmnOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $bpmnOBJ->setFechahoramod('0000-00-00 00:00:00');
        try {
            $this->DAO->subirBPMN($bpmnOBJ);
            $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE REGISTRADO EN JIMSOFT');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " subirppmnAction - ArchivoController->subirppmn \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EL ARCHIVO NO FUE REGISTRADO EN JIMSOFT.');
        }
        return $this->redirect()->toUrl('index');
    }
    public function cambiarbpmnAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $id_bpmn = (int) $this->params()->fromQuery('id_bpmn', 0);
            $idLvmen = (int) $this->params()->fromQuery('idLvmen', 0);
            $infoBPMN = $this->DAO->getBpmnDetalle($id_bpmn, $idLvmen);
            $form = new BpmnForm('cambiarbpmn');
            $form->setData($infoBPMN);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new BpmnForm('cambiarbpmn');
        $bpmnOBJ = new Bpmn();
        $form->setInputFilter($bpmnOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            /*  print_r($form->getMessages());
            return ['form' => $form]; */
            $this->flashMessenger()->addErrorMessage('LA INFORMACION A GUARDAR NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        //----------------------------------------------------------------------
        try {
            $bpmnOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $bpmnOBJ->setModificadopor($modificadopor);
            $bpmnOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->editarBPMN($bpmnOBJ);
            $this->flashMessenger()->addSuccessMessage('LA INFORMACION DEL ARCHIVO FUE ACTUALIZADA');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " cambiarbpmnAction - ArchivoController->cambiarbpmn \n"
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
    public function actualizarbpmnAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $id_bpmn = (int) $this->params()->fromQuery('id_bpmn', 0);
            $idLvmen = (int) $this->params()->fromQuery('idLvmen', 0);
            $infoBPMN = $this->DAO->getBpmnDetalle($id_bpmn, $idLvmen);
            $form = new BpmnForm('actualizarbpmn');
            $form->setData($infoBPMN);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new BpmnForm('actualizarbpmn');
        $bpmnOBJ = new Bpmn();
        $form->setInputFilter($bpmnOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            /*  print_r($form->getMessages());
            return ['form' => $form]; */
            $this->flashMessenger()->addErrorMessage('LA INFORMACION A GUARDAR NO ES VALIDA');
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
            $extensiones = new \Laminas\Validator\File\Extension(array('extension' => array('pdf,docx,xlsx,pptx')));
            if (!$extensiones->isValid($files['archivo'])) {
                $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO NO ES PERMITIDO. <br> ARCHIVOS PERMITIDOS: <br> txt');
                return $this->redirect()->toUrl('index');
            }
            $filter = new \Laminas\Filter\File\RenameUpload([
                'target' => $this->rutaArchivos . $files['archivo']['name'],
                'randomize' => false,
                'overwrite' => true,
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
        //----------------------------------------------------------------------
        try {
            $bpmnOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $bpmnOBJ->setModificadopor($modificadopor);
            $bpmnOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $bpmnOBJ->setArchivo($respaldo);
            $this->DAO->editarBPMN($bpmnOBJ);
            $this->flashMessenger()->addSuccessMessage('LA INFORMACION DEL ARCHIVO FUE ACTUALIZADA');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " cambiarbpmnAction - ArchivoController->cambiarbpmn \n"
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
}
