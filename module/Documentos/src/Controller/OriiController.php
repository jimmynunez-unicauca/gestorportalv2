<?php

declare(strict_types=1);

namespace Documentos\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Documentos\Modelo\DAO\OriiDAO;
use Documentos\Formularios\OriiForm;
use Documentos\Formularios\OriidocumentosForm;
use Documentos\Modelo\Entidades\Orii;
use Documentos\Modelo\Entidades\Oriidocumentos;

class OriiController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = './public/log/';
    private $rutaArchivos = '/var/www/html/newportal/archivos/orii/';
    /* private $rutaArchivos = './../archivos/orii/'; */
    //------------------------------------------------------------------------------

    public function __construct(OriiDAO $dao)
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
            $infoSesion['idUsuario'] = $auth->getIdentity()->idUsuario;
        }
        return $infoSesion;
    }

    //------------------------------------------------------------------------------
    function getFormulario($action = '', $idOrii = 0)
    {
        $form = new OriiForm($action);
        if ($idOrii != 0) {
            $oriiOBJ = $this->DAO->getOrii($idOrii);
            $form->bind($oriiOBJ);
        }
        return $form;
    }
    //------------------------------------------------------------------------------
    public function indexAction()
    {
        return new ViewModel([
            'fetchAll' => $this->DAO->fetchAll(''),
        ]);
    }

    //------------------------------------------------------------------------------
    public function registrarAction()
    {
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];
        $insti = $this->DAO->getInstituciones();
        $listaInsti = array();
        foreach ($insti as $in) {
            $listaInsti[$in['idInstituto']] = $in['institucion'] . " (" . $in['pais'] . ")";
        }
        //----------------------------------------------------------------------
        $form = new OriiForm('registrar', $listaInsti);
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $oriiOBJ = new Orii();
        $form->setInputFilter($oriiOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            print_r($form->getMessages());
            return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL ARCHIVO NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        $oriiOBJ->exchangeArray($form->getData());
        $oriiOBJ->setEstado('Vigente');
        $oriiOBJ->setRegistradopor($registradopor);
        $oriiOBJ->setModificadopor('');
        $oriiOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $oriiOBJ->setFechahoramod('0000-00-00 00:00:00');
        try {
            $this->DAO->registrar($oriiOBJ);
            $this->flashMessenger()->addSuccessMessage('ORII FUE REGISTRADO CON EXITO');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR ORII - OriiController->registrar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! ORII NO FUE REGISTRADO.');
        }
        return $this->redirect()->toUrl('index');
    }
    //------------------------------------------------------------------------------  
    public function editarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idOrii = (int) $this->params()->fromQuery('idOrii', 0);
            $insti = $this->DAO->getInstituciones();
            $listaInsti = array();
            foreach ($insti as $in) {
                $listaInsti[$in['idInstituto']] = $in['institucion'] . " (" . $in['pais'] . ")";
            }
            $infoOrii = $this->DAO->getOriiDetalle($idOrii);
            $form = new OriiForm('editar', $listaInsti);
            $form->setData($infoOrii);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new OriiForm('editar');
        $clOBJ = new Orii();
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
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ACTUALIZAR ORII - OriiController->editar \n"
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
        $idOrii = (int) $this->params()->fromQuery('idOrii', 0);
        $infoOrii = $this->DAO->getOriiDetalle($idOrii);
        $orriDocumentos = $this->DAO->getOriiDocumentos($idOrii);
        $view = new ViewModel([
            'form' => $infoOrii,
            'orriDocumentos' => $orriDocumentos,
        ]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------
    public function eliminardocAction()
    {
        $id_documentos_orii = (int) $this->params()->fromQuery('id_documentos_orii', 0);
        if ($id_documentos_orii != 0) {
            try {
                $this->DAO->eliminarDoc($id_documentos_orii);
                $this->flashMessenger()->addSuccessMessage('LA DEPENDENCIA FUE ELIMINADA CON EXITO');
            } catch (\Exception $ex) {
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN ERROR ' . $ex);
            }
        }
        return new JsonModel([]);
    }
    //------------------------------------------------------------------------------  
    public function verArchivoAction()
    {
        $idOrii = (int) $this->params()->fromRoute('id1', 0);
        $archivos = $this->DAO->getOriiDocumentos($idOrii);
        $archivo = $archivos[0]['documento'];
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
    public function subirdocumentoAction()
    {
        $idOrii = (int) $this->params()->fromQuery('idOrii', 0);
        $orriDocumentos = $this->DAO->getOriiDocumentos($idOrii);
        $oriiDetalle = $this->DAO->getOriiDetalle($idOrii);
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];
        //----------------------------------------------------------------------
        $form = new OriidocumentosForm('subirdocumento');
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel([
                'form' => $form,
                'orriDocumentos' => $orriDocumentos,
                'oriiDetalle' => $oriiDetalle,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $oriiOBJ = new Oriidocumentos();
        $form->setInputFilter($oriiOBJ->getInputFilter());
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
        if (!$uploadOK->isValid($files['documento'])) {
            $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO PRESENTA ERRORES AL CARGAR AL SERVIDOR');
            return $this->redirect()->toUrl('index');
        }
        if (array_key_exists('documento', $files)) {
            $ext = pathinfo($files['documento']['name'], PATHINFO_EXTENSION);
            $filesize = new \Laminas\Validator\File\Size([
                'min' => '250B',
                'max' => '25MB',
            ]);
            if (!$filesize->isValid($files['documento'])) {
                $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO NO ESTA EN LOS LIMITES PERMITIDOS. <br> MINIMO: 250B  <br> MAXIMO: <b>25MB</b>');
                return $this->redirect()->toUrl('index');
            }
            $extensiones = new \Laminas\Validator\File\Extension(array('extension' => array('pdf')));
            if (!$extensiones->isValid($files['documento'])) {
                $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO NO ES PERMITIDO. <br> ARCHIVOS PERMITIDOS: <br> PDF');
                return $this->redirect()->toUrl('index');
            }
            $filter = new \Laminas\Filter\File\RenameUpload([
                'target' => $this->rutaArchivos . 'ORII' . '.' . $ext,
                'randomize' => true,
            ]);
            //----------------------------------------------------------------------
            $upload = $filter->filter($files['documento']);
            //----------------------------------------------------------------------
            if ($upload['error'] != 0) {
                $this->flashMessenger()->addErrorMessage('NO FUE POSIBLE SUBIR EL ARCHIVO DE RESPALDO ADJUNTO.');
                return $this->redirect()->toUrl('index');
            }
            $respaldo = basename($upload['tmp_name']);
            //----------------------------------------------------------------------
        }
        $oriiOBJ->exchangeArray($form->getData());
        $oriiOBJ->setDocumento($respaldo);
        $oriiOBJ->setEstado_documento('Activo');
        $oriiOBJ->setRegistradopor_documento($registradopor);
        $oriiOBJ->setFechahorareg_documento(date('Y-m-d H:i:s'));
        try {
            $this->DAO->registrarDocumento($oriiOBJ);
            $this->flashMessenger()->addSuccessMessage('EL DOCUMENTO DE ORII FUE REGISTRADO CON EXITO');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR DOCUMENTO ORII - OriiController->subirdocumento \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EL DOCUMENTO DE ORII NO FUE REGISTRADO.');
        }
        return $this->redirect()->toUrl('index');
    }
    //------------------------------------------------------------------------------
}
