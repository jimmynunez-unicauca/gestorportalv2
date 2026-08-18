<?php

declare(strict_types=1);

namespace Administracion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Administracion\Modelo\DAO\VriemprendimientosDAO;
use Administracion\Formularios\VriemprendimientosForm;
use Administracion\Modelo\Entidades\Vriemprendimientos;

class VriemprendimientosController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = './public/log/';
    private $rutaArchivos = '/var/www/html/newportal/archivos/vri_emprendimientos/';
    //private $rutaArchivos = './../newportal/archivos/vri_emprendimientos/';
    //------------------------------------------------------------------------------

    public function __construct(VriemprendimientosDAO $dao)
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
        $form = new VriemprendimientosForm($action);
        if ($id != 0) {
            $vriemprendimientosOBJ = $this->DAO->getVriemprendimientos($id);
            $form->bind($vriemprendimientosOBJ);
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
    public function detalleAction()
    {
        $id = (int) $this->params()->fromQuery('id', 0);
        $dependencias = $this->DAO->getVriemprendimientosDetalle($id);
        $view = new ViewModel(['form' => $dependencias]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------
    public function registrarAction()
    {
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];
        //----------------------------------------------------------------------
        $form = new VriemprendimientosForm('registrar');
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $vriemprendimientosOBJ = new Vriemprendimientos();
        $form->setInputFilter($vriemprendimientosOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            print_r($form->getMessages());
            return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL EMPRENDIMIENTO NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        //----------------------------------------------------------------------
        $files = $request->getFiles()->toArray();
        $respaldo = $this->procesarArchivo($files, 'imagen', ['jpg', 'jpeg', 'png', 'gif'], '250B', '500KB', 'VRI', 'sinImagen.png');
        if ($respaldo === false) return $this->redirect()->toUrl('index');
        //----------------------------------------------------------------------
        $vriemprendimientosOBJ->exchangeArray($form->getData());
        $vriemprendimientosOBJ->setImagen($respaldo);
        $vriemprendimientosOBJ->setEstado('Activo');
        $vriemprendimientosOBJ->setRegistradopor($registradopor);
        $vriemprendimientosOBJ->setModificadopor('');
        $vriemprendimientosOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $vriemprendimientosOBJ->setFechahoramod('0000-00-00 00:00:00');
        try {
            $this->DAO->registrar($vriemprendimientosOBJ);
            $this->flashMessenger()->addSuccessMessage('EL EMPRENDIMIENTO FUE REGISTRADO EN GESTORPORTALV2');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR EMPRENDIMIENTO - VriemprendimientosController->registrar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EL EMPRENDIMIENTO NO FUE REGISTRADO EN GESTORPORTALV2.');
        }
        return $this->redirect()->toUrl('index');
    }
    //------------------------------------------------------------------------------
    public function editarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);
            $infoDirectorio = $this->DAO->getVriemprendimientosDetalle($id);
            $form = new VriemprendimientosForm('editar');
            $form->setData($infoDirectorio);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new VriemprendimientosForm('editar');
        $vriemprendimientosOBJ = new Vriemprendimientos();
        $form->setInputFilter($vriemprendimientosOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            /*  print_r($form->getMessages());
            return ['form' => $form]; */
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL EMPRENDIMIENTO NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        //----------------------------------------------------------------------
        try {
            $vriemprendimientosOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $vriemprendimientosOBJ->setModificadopor($modificadopor);
            $vriemprendimientosOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->editar($vriemprendimientosOBJ);
            $this->flashMessenger()->addSuccessMessage('LA INFORMACION DEL EMPRENDIMIENTO FUE ACTUALIZADA');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ACTUALIZAR EMPRENDIMIENTO VriemprendimientosController->editar \n"
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
    public function actualizararchivoAction()
    {
        $id = (int) $this->params()->fromQuery('id', 0);
        $form = $this->getFormulario('actualizararchivo', $id);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $infosesion = $this->getInfoSesion();
                $registradopor = $infosesion['login'];
                $vriemprendimientosOBJ = new Vriemprendimientos($form->getData());
                //----------------------------------------------------------------------
                $files = $request->getFiles()->toArray();
                $respaldo = $this->procesarArchivo($files, 'imagen', ['jpg', 'jpeg', 'png', 'gif'], '250B', '500KB', 'VRI', 'sinImagen.png');
                if ($respaldo === false) return $this->redirect()->toUrl('index');
                //----------------------------------------------------------------------
                $vriemprendimientosOBJ->setImagen($respaldo);
                $vriemprendimientosOBJ->setModificadopor($registradopor);
                $vriemprendimientosOBJ->setFechahoramod(date('Y-m-d H:i:s'));
                try {
                    $this->DAO->editar($vriemprendimientosOBJ);
                    $this->flashMessenger()->addSuccessMessage('LA IMAGEN DEL EMPRENDIMIENTO FUE ACTUALIZADA EN GESTORPORTALV2');
                    return $this->redirect()->toUrl('index');
                } catch (\Exception $ex) {
                    $msgLog = "\n" . date('Y-m-d H:i:s') . " EDITAR Archivo - VriemprendimientosController->actualizararchivo \n"
                        . $ex->getMessage()
                        . "\n----------------------------------------------------------------------- \n";
                    $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
                    fwrite($file, $msgLog);
                    fclose($file);
                    $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>LA IMAGEN DEL EMPRENDIMIENTO NO FUE ACTUALIZADA EN GESTORPORTALV2.');
                }
            } else {
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE, LA IMAGEN DEL EMPRENDIMIENTO NO FUE ACTUALIZADA EN GESTORPORTALV2');
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
    public function desactivarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);
            $infoPfi = $this->DAO->getVriemprendimientosDetalle($id);
            $form = new VriemprendimientosForm('desactivar');
            $form->setData($infoPfi);
            $view = new ViewModel([
                'form' => $form,
                'estado' => 'Eliminado',
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $id = (int) $this->params()->fromPost('id', 0);

        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE EMPRENDIMIENTO NO VÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        try {
            $this->DAO->cambiarEstado($id, 'Eliminado');
            $this->flashMessenger()->addSuccessMessage('EL EMPRENDIMIENTO FUE DESACTIVADO EXITOSAMENTE');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " DESACTIVAR EMPRENDIMIENTO - VriemprendimientosController->desactivar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EL EMPRENDIMIENTO NO FUE DESACTIVADO.');
        }

        return $this->redirect()->toUrl('index');
    }
    public function activarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);
            $infoPfi = $this->DAO->getVriemprendimientosDetalle($id);

            $form = new VriemprendimientosForm('activar');
            $form->setData($infoPfi);

            $view = new ViewModel([
                'form' => $form,
                'estado' => 'Activo',
            ]);
            $view->setTerminal(true);
            return $view;
        }

        //----------------------------------------------------------------------
        $id = (int) $this->params()->fromPost('id', 0);

        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE EMPRENDIMIENTO NO VÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        try {
            $this->DAO->cambiarEstado($id, 'Activo');
            $this->flashMessenger()->addSuccessMessage('EL EMPRENDIMIENTO FUE ACTIVADO EXITOSAMENTE');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ACTIVAR EMPRENDIMIENTO - VriemprendimientosController->activar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EL EMPRENDIMIENTO NO FUE ACTIVADO.');
        }

        return $this->redirect()->toUrl('index');
    }
    //------------------------------------------------------------------------------
    public function eliminarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);
            $infoPfi = $this->DAO->getVriemprendimientos($id);
            $form = new VriemprendimientosForm('eliminar');
            /*  $form->setData($infoPfi); */
            $form->bind($infoPfi);
            $view = new ViewModel([
                'form' => $form,
                'estado' => 'Eliminado',
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new VriemprendimientosForm('eliminar');
        $usOBJ = new Vriemprendimientos();
        $form->setInputFilter($usOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            print_r($form->getMessages());
            return ['form' => $form];
            exit();
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE ELIMINACION DEL FORMULARIO NO ES VALIDA');
            return $this->redirect()->toUrl('index?id=' . $this->params()->fromPost('id_config', 0));
        }
        //----------------------------------------------------------------------
        try {
            $usOBJ->exchangeArray($form->getData());
            $this->DAO->eliminar($usOBJ);
            $this->flashMessenger()->addSuccessMessage('EL FORMULARIO FUE ELIMINADO DE GESTORPORTAL');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ELIMINAR FORMULARIO - VriemprendimientosController->eliminar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL FORMULARIO NO FUE ELIMINADO DE GESTORPORTAL.');
        }
        return $this->redirect()->toUrl('index?id=' . $usOBJ->getId());
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
