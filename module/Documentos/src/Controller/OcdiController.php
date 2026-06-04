<?php

declare(strict_types=1);

namespace Documentos\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Documentos\Modelo\DAO\OcdiDAO;
use Documentos\Formularios\OcdiForm;
use Documentos\Modelo\Entidades\Ocdi;

class OcdiController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = './public/log/';
    private $rutaArchivos = '/var/www/html/newportal/archivos/ocdi/revistas/documentos/';
    private $rutaImagenes = '/var/www/html/newportal/archivos/ocdi/revistas/imagenes/';
    /* private $rutaArchivos = './../archivos/ocdi/'; */
    //------------------------------------------------------------------------------

    public function __construct(OcdiDAO $dao)
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
    function getFormulario($action = '', $idOcdi = 0)
    {
        $form = new OcdiForm($action);
        if ($idOcdi != 0) {
            $ocdiOBJ = $this->DAO->getOcdi($idOcdi);
            $form->bind($ocdiOBJ);
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

        $form = new OcdiForm('registrar');
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }

        $ocdiOBJ = new Ocdi();
        $form->setInputFilter($ocdiOBJ->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            print_r($form->getMessages());
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL ARCHIVO NO ES VALIDA');
            return ['form' => $form];
        }

        $files = $request->getFiles()->toArray();

        // === Validación y subida del archivo "documento" obligatorio ===
        if (!isset($files['documento']) || $files['documento']['error'] !== 0) {
            $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO NO FUE CARGADO CORRECTAMENTE.');
            return $this->redirect()->toUrl('index');
        }

        $uploadOK = new \Laminas\Validator\File\UploadFile();
        if (!$uploadOK->isValid($files['documento'])) {
            $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO PRESENTA ERRORES AL CARGAR AL SERVIDOR');
            return $this->redirect()->toUrl('index');
        }

        $ext = pathinfo($files['documento']['name'], PATHINFO_EXTENSION);
        $filesize = new \Laminas\Validator\File\Size(['min' => '250B', 'max' => '25MB']);
        if (!$filesize->isValid($files['documento'])) {
            $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO NO ESTA EN LOS LIMITES PERMITIDOS. <br> MINIMO: 250B  <br> MAXIMO: <b>25MB</b>');
            return $this->redirect()->toUrl('index');
        }

        $extensiones = new \Laminas\Validator\File\Extension(['extension' => ['pdf', 'docx', 'xlsx', 'pptx']]);
        if (!$extensiones->isValid($files['documento'])) {
            $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO NO ES PERMITIDO. <br> ARCHIVOS PERMITIDOS: <br> PDF, DOCX, XLSX, PPTX');
            return $this->redirect()->toUrl('index');
        }

        $filter = new \Laminas\Filter\File\RenameUpload([
            'target' => $this->rutaArchivos . 'OCDI_' . time() . '.' . $ext,
            'randomize' => true,
        ]);
        $upload = $filter->filter($files['documento']);

        if ($upload['error'] != 0) {
            $this->flashMessenger()->addErrorMessage('NO FUE POSIBLE SUBIR EL ARCHIVO DE RESPALDO ADJUNTO.');
            return $this->redirect()->toUrl('index');
        }

        $respaldo = basename($upload['tmp_name']);

        // === Validación y subida de la imagen opcional ===
        $imagen = 'sinImagen.png'; // valor por defecto
        if (isset($files['imagen']) && $files['imagen']['error'] === 0) {
            $imagenExt = pathinfo($files['imagen']['name'], PATHINFO_EXTENSION);
            $validaImgExt = new \Laminas\Validator\File\Extension(['extension' => ['jpg', 'jpeg', 'png', 'gif']]);
            if ($validaImgExt->isValid($files['imagen'])) {
                $filterImg = new \Laminas\Filter\File\RenameUpload([
                    'target' => $this->rutaImagenes . 'OCDI_' . time() . '.' . $imagenExt,
                    'randomize' => true,
                ]);
                $uploadImg = $filterImg->filter($files['imagen']);

                if ($uploadImg['error'] === 0) {
                    $imagen = basename($uploadImg['tmp_name']);
                }
            }
        }

        // === Guardar en la base de datos ===
        $ocdiOBJ->exchangeArray($form->getData());
        $ocdiOBJ->setDocumento($respaldo);
        $ocdiOBJ->setImagen($imagen);
        $ocdiOBJ->setEstado('Activo');
        $ocdiOBJ->setRegistradopor($registradopor);
        $ocdiOBJ->setModificadopor('');
        $ocdiOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $ocdiOBJ->setFechahoramod('0000-00-00 00:00:00');

        try {
            $this->DAO->registrar($ocdiOBJ);
            $this->flashMessenger()->addSuccessMessage('OCDI FUE REGISTRADO CON EXITO');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR OCDI - OcdiController->registrar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! OCDI NO FUE REGISTRADO.');
        }

        return $this->redirect()->toUrl('index');
    }
    //------------------------------------------------------------------------------  
    public function editarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idOcdi = (int) $this->params()->fromQuery('idOcdi', 0);
            $infoOcdi = $this->DAO->getOcdiDetalle($idOcdi);
            $form = new OcdiForm('editar');
            $form->setData($infoOcdi);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new OcdiForm('editar');
        $clOBJ = new Ocdi();
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
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ACTUALIZAR OCDI - OcdiController->editar \n"
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
        $idOcdi = (int) $this->params()->fromQuery('idOcdi', 0);
        $infoOcdi = $this->DAO->getOcdiDetalle($idOcdi);
        $view = new ViewModel([
            'form' => $infoOcdi,
        ]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------
    public function verArchivoAction()
    {
        $idOcdi = (int) $this->params()->fromRoute('id1', 0);
        $archivos = $this->DAO->getOcdiDetalle($idOcdi);
        $archivo = $archivos['documento'];
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
}
