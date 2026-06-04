<?php

declare(strict_types=1);

namespace Documentos\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Documentos\Modelo\DAO\UnisaludtransparenciaDAO;
use Documentos\Formularios\UnisaludtransparenciaForm;
use Documentos\Modelo\Entidades\Unisaludtransparencia;

class UnisaludtransparenciaController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = './public/log/';
    private $rutaArchivos = '/var/www/html/newportal/archivos/unisalud/transparencia/';
    //------------------------------------------------------------------------------

    public function __construct(UnisaludtransparenciaDAO $dao)
    {
        $this->DAO = $dao;
    }

    //------------------------------------------------------------------------------

    public function getInfoSesion()
    {
        $infoSesion = [
            'idEmpleadoCliente' => 0,
            'idUsuario' => 0,
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
    function getFormulario($action = '', $id = 0)
    {
        $form = new UnisaludtransparenciaForm($action);
        if ($id != 0) {
            $usOBJ = $this->DAO->getUnisaludtransparencia($id);
            $form->bind($usOBJ);
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
        $registradopor = $infosesion['idUsuario'];
        //----------------------------------------------------------------------
        $form = new UnisaludtransparenciaForm('registrar');
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $usOBJ = new Unisaludtransparencia();
        $form->setInputFilter($usOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            /* print_r($form->getMessages());
            return ['form' => $form];
            exit(); */
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        //----------------------------------------------------------------------
        $files = $request->getFiles()->toArray();
        $respaldo = $this->procesarArchivo($files, 'ruta_archivo', ['pdf', 'docx', 'xlsx', 'pptx'], '250B', '25MB', 'UT', 'sinArchivo.pdf');
        if ($respaldo === false) return $this->redirect()->toUrl('index');
        //----------------------------------------------------------------------
        $usOBJ->exchangeArray($form->getData());
        $usOBJ->setRuta_archivo($respaldo);
        $usOBJ->setCreado_por($registradopor);
        $usOBJ->setCreado_el(date('Y-m-d H:i:s'));
        $usOBJ->setActualizado_el('0000-00-00 00:00:00');
        try {
            $this->DAO->registrar($usOBJ);
            $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE REGISTRADO EN GESTORPORTAL');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR ARCHIVO - UnisaludtransparenciaController->registrar \n"
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
            $form = new UnisaludtransparenciaForm('editar');
            $form->setData($infoArchivo);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new UnisaludtransparenciaForm('editar');
        $clOBJ = new Unisaludtransparencia();
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
            $modificadopor = $infosesion['idUsuario'];
            $clOBJ->setActualizado_por($modificadopor);
            $clOBJ->setActualizado_el(date('Y-m-d H:i:s'));
            $this->DAO->editar($clOBJ);
            $this->flashMessenger()->addSuccessMessage('LA INFORMACION DEL ARCHIVO FUE ACTUALIZADA');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ACTUALIZAR CONTRATO LABORAL - UnisaludtransparenciaController->editar \n"
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
            $form = new UnisaludtransparenciaForm('eliminar');
            $form->setData($infoArchivo);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new UnisaludtransparenciaForm('eliminar');
        $usOBJ = new Unisaludtransparencia();
        $form->setInputFilter($usOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            //print_r($form->getMessages());
            //return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE ELIMINACION DEL ARCHIVO NO ES VALIDA');
            return $this->redirect()->toUrl('index?id=' . $this->params()->fromPost('id', 0));
        }
        //----------------------------------------------------------------------
        try {
            $usOBJ->exchangeArray($form->getData());
            $this->DAO->eliminar($usOBJ);
            $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE ELIMINADA DE GESTORPORTAL');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ELIMINAR ARCHIVO - UnisaludtransparenciaController->eliminar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL ARCHIVO NO FUE ELIMINADA DE GESTORPORTAL.');
        }
        return $this->redirect()->toUrl('index?id=' . $usOBJ->getid());
    }
    //------------------------------------------------------------------------------   
    public function activarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);
            $infoPluginunicauca = $this->DAO->getArchivoDetalle($id);

            $form = new UnisaludtransparenciaForm('activar');
            $form->setData($infoPluginunicauca);

            $view = new ViewModel([
                'form' => $form,
                'accion' => 'activar'
            ]);
            $view->setTerminal(true);
            return $view;
        }

        //----------------------------------------------------------------------
        $id = (int) $this->params()->fromPost('id', 0);

        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE DOCUMENTO NO VÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        try {
            $this->DAO->cambiarEstado($id, 1);
            $this->flashMessenger()->addSuccessMessage('EL DOCUMENTO FUE ACTIVADO EXITOSAMENTE');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ACTIVAR DOCUMENTO - UnisaludtransparenciaController->activar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EL DOCUMENTO NO FUE ACTIVADO.');
        }

        return $this->redirect()->toUrl('index');
    }
    //------------------------------------------------------------------------------  
    public function desactivarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);
            $infoPluginunicauca = $this->DAO->getArchivoDetalle($id);

            $form = new UnisaludtransparenciaForm('desactivar');
            $form->setData($infoPluginunicauca);

            $view = new ViewModel([
                'form' => $form,
                'accion' => 'desactivar'
            ]);
            $view->setTerminal(true);
            return $view;
        }

        //----------------------------------------------------------------------
        $id = (int) $this->params()->fromPost('id', 0);

        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE DOCUMENTO NO VÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        try {
            $this->DAO->cambiarEstado($id, 0);
            $this->flashMessenger()->addSuccessMessage('EL DOCUMENTO FUE DESACTIVADO EXITOSAMENTE');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " DESACTIVAR DOCUMENTO - UnisaludtransparenciaController->desactivar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EL DOCUMENTO NO FUE DESACTIVADO.');
        }

        return $this->redirect()->toUrl('index');
    }
    //------------------------------------------------------------------------------  
    public function verArchivoAction()
    {
        $id = (int) $this->params()->fromRoute('id1', 0);
        $archivos = $this->DAO->getArchivoDetalle($id);
        $archivo = $archivos['ruta_archivo'];
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
                $usOBJ = new Unisaludtransparencia($form->getData());
                //----------------------------------------------------------------------
                $files = $request->getFiles()->toArray();
                $respaldo = $this->procesarArchivo($files, 'ruta_archivo', ['pdf', 'docx', 'xlsx', 'pptx'], '250B', '25MB', 'UT', 'sinArchivo.pdf');
                if ($respaldo === false) return $this->redirect()->toUrl('index');
                //----------------------------------------------------------------------
                $usOBJ->setRuta_archivo($respaldo);
                $usOBJ->setActualizado_por($session['idUsuario']);
                $usOBJ->setActualizado_el(date('Y-m-d H:i:s'));
                try {
                    $this->DAO->editar($usOBJ);
                    $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE EDITADO EN GESTORPORTAL');
                    return $this->redirect()->toUrl('index');
                } catch (\Exception $ex) {
                    $msgLog = "\n" . date('Y-m-d H:i:s') . " EDITAR Archivo - UnisaludtransparenciaController->registrar \n"
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
    private function procesarArchivo($files, $nombreCampo, $extensionesPermitidas, $tamanoMin, $tamanoMax, $directorio, $default)
    {
        if (isset($files[$nombreCampo]) && $files[$nombreCampo]['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadOK = new \Laminas\Validator\File\UploadFile();
            if (!$uploadOK->isValid($files[$nombreCampo])) {
                $this->flashMessenger()->addErrorMessage("ERROR AL CARGAR EL ARCHIVO DE {$nombreCampo}");
                return false;
            }

            $sizeValidator = new \Laminas\Validator\File\Size(['min' => $tamanoMin, 'max' => $tamanoMax]);
            if (!$sizeValidator->isValid($files[$nombreCampo])) {
                $this->flashMessenger()->addErrorMessage("ARCHIVO DE {$nombreCampo} FUERA DE RANGO DE TAMAÑO PERMITIDO ({$tamanoMin} a {$tamanoMax}).");
                return false;
            }

            $extValidator = new \Laminas\Validator\File\Extension(['extension' => $extensionesPermitidas]);
            if (!$extValidator->isValid($files[$nombreCampo])) {
                $exts = implode(', ', $extensionesPermitidas);
                $this->flashMessenger()->addErrorMessage("EXTENSIÓN DE {$nombreCampo} NO PERMITIDA. PERMITIDOS: {$exts}");
                return false;
            }

            $ext = pathinfo($files[$nombreCampo]['name'], PATHINFO_EXTENSION);
            $filter = new \Laminas\Filter\File\RenameUpload([
                'target' => $this->rutaArchivos . "{$directorio}_" . time() . '.' . $ext,
                'randomize' => true,
            ]);
            $upload = $filter->filter($files[$nombreCampo]);

            if ($upload['error'] != 0) {
                $this->flashMessenger()->addErrorMessage("NO FUE POSIBLE SUBIR EL ARCHIVO DE {$nombreCampo}");
                return false;
            }
            return basename($upload['tmp_name']);
        }

        return $default;
    }
    //------------------------------------------------------------------------------     
}
