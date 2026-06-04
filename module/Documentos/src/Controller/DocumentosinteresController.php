<?php

declare(strict_types=1);

namespace Documentos\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Documentos\Modelo\DAO\DocumentosinteresDAO;
use Documentos\Formularios\DocumentosinteresForm;
use Documentos\Modelo\Entidades\Documentosinteres;

class DocumentosinteresController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = './public/log/';
    private $rutaArchivos = '/var/www/html/newportal/archivos/lumen/';
    /* private $rutaArchivos = './../archivos/lumen/'; */
    //------------------------------------------------------------------------------

    public function __construct(DocumentosinteresDAO $dao)
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
        //----------------------------------------------------------------------
        $form = new DocumentosinteresForm('registrar');
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $docOBJ = new Documentosinteres();
        $form->setInputFilter($docOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            print_r($form->getMessages());
            return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL ARCHIVO NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        $docOBJ->exchangeArray($form->getData());
        $docOBJ->setEstado('Activo');
        $docOBJ->setRegistradopor($registradopor);
        $docOBJ->setModificadopor('');
        $docOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $docOBJ->setFechahoramod('0000-00-00 00:00:00');
        try {
            $this->DAO->registrar($docOBJ);
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
    public function detalleAction()
    {
        $idDocumentosInteres = (int) $this->params()->fromQuery('idDocumentosInteres', 0);
        $infoEmpleado = $this->DAO->getDocDetalle($idDocumentosInteres);
        $view = new ViewModel(['form' => $infoEmpleado]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------
    public function eliminarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idDocumentosInteres = (int) $this->params()->fromQuery('idDocumentosInteres', 0);
            $infoLven = $this->DAO->getDocDetalle($idDocumentosInteres);
            $form = new DocumentosinteresForm('eliminar');
            $form->setData($infoLven);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new DocumentosinteresForm('eliminar');
        $docOBJ = new Documentosinteres();
        $form->setInputFilter($docOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            //print_r($form->getMessages());
            //return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE ELIMINACION DEL ARCHIVO NO ES VALIDA');
            return $this->redirect()->toUrl('index?idDocumentosInteres=' . $this->params()->fromPost('idDocumentosInteres', 0));
        }
        //----------------------------------------------------------------------
        try {
            $docOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $docOBJ->setModificadopor($modificadopor);
            $docOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->eliminar($docOBJ);
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
        return $this->redirect()->toUrl('index?idDocumentosInteres=' . $docOBJ->getIdDocumentosInteres());
    }
    //------------------------------------------------------------------------------  
    public function verArchivoAction()
    {
        $id = (int) $this->params()->fromRoute('id1', 0);
        $archivos = $this->DAO->getDocDetalle($id);
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
    public function buscardocumentoAction()
    {
        $view = new ViewModel([
            'lvmen' => $this->DAO->getLvmen(),
        ]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------
}
