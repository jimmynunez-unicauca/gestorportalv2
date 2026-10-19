<?php

declare(strict_types=1);

namespace Administracion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Administracion\Modelo\DAO\VriconvocatoriasDAO;
use Administracion\Formularios\VriconvocatoriasForm;
use Administracion\Modelo\Entidades\Vriconvocatorias;

class VriconvocatoriasController extends AbstractActionController
{
    private $DAO;
    private $rutaLog = './public/log/';
    //private $rutaArchivos = '/var/www/html/newportal/archivos/vri_proyectos/';
    private $rutaArchivos = './../newportal/archivos/vri_proyectos/';

    public function __construct(VriconvocatoriasDAO $dao)
    {
        $this->DAO = $dao;
    }

    public function getInfoSesion()
    {
        $infoSesion = ['idEmpleadoCliente' => 0, 'login' => 'SIN INICIO DE SESION'];
        $auth = new AuthenticationService();
        if ($auth->hasIdentity()) {
            $infoSesion['login'] = $auth->getIdentity()->login;
            $infoSesion['idEmpleadoCliente'] = $auth->getIdentity()->idEmpleadoCliente;
        }
        return $infoSesion;
    }

    private function getFormulario($action = '', $id = 0)
    {
        $form = new VriconvocatoriasForm($action);
        if ($id != 0) {
            $OBJ = $this->DAO->getVriconvocatoria($id);
            if ($OBJ) $form->bind($OBJ);
        }
        return $form;
    }

    public function indexAction()
    {
        return new ViewModel([
            'fetchAll' => $this->DAO->fetchAll(),
        ]);
    }

    public function detalleAction()
    {
        $id = (int) $this->params()->fromQuery('id', 0);
        $detalle = $this->DAO->getVriconvocatoriaDetalle($id);
        $view = new ViewModel(['detalle' => $detalle, 'id' => $id]);
        $view->setTerminal(true);
        return $view;
    }

    public function registrarAction()
    {
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];

        $form = new VriconvocatoriasForm('registrar');
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }

        $vriconvocatoriasOBJ = new Vriconvocatorias();
        $form->setInputFilter($vriconvocatoriasOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }

        $files = $request->getFiles()->toArray();
        $documento = $this->procesarArchivo(
            $files,
            'documento',
            ['pdf', 'doc', 'docx'],
            '250B',
            '5MB',
            'CONV',
            null
        );
        if ($documento === false) return $this->redirect()->toUrl('index');

        $vriconvocatoriasOBJ->exchangeArray($form->getData());
        $vriconvocatoriasOBJ->setDocumento($documento);
        $vriconvocatoriasOBJ->setEstado('Activo');
        $vriconvocatoriasOBJ->setRegistradopor($registradopor);
        $vriconvocatoriasOBJ->setModificadopor('');
        $vriconvocatoriasOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $vriconvocatoriasOBJ->setFechahoramod('0000-00-00 00:00:00');
        try {
            $this->DAO->registrar($vriconvocatoriasOBJ);
            $this->flashMessenger()->addSuccessMessage('LA CONVOCATORIA FUE REGISTRADA EN GESTORPORTALV2');
        } catch (\Exception $ex) {
            $this->logError('REGISTRAR CONVOCATORIA - VriconvocatoriasController->registrar', $ex);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! LA CONVOCATORIA NO FUE REGISTRADA.');
        }
        return $this->redirect()->toUrl('index');
    }

    public function editarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);
            $info = $this->DAO->getVriconvocatoriaDetalle($id);
            if (!$info) {
                $this->flashMessenger()->addErrorMessage('CONVOCATORIA NO ENCONTRADA');
                return $this->redirect()->toUrl('index');
            }
            $form = new VriconvocatoriasForm('editar');
            $form->setData($info);
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }

        $form = new VriconvocatoriasForm('editar');
        $vriconvocatoriasOBJ = new Vriconvocatorias();
        $form->setInputFilter($vriconvocatoriasOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            $this->flashMessenger()->addErrorMessage('LA INFORMACIÓN DE LA CONVOCATORIA NO ES VÁLIDA');
            return $this->redirect()->toUrl('index');
        }
        try {
            $vriconvocatoriasOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $vriconvocatoriasOBJ->setModificadopor($infosesion['login']);
            $vriconvocatoriasOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->editar($vriconvocatoriasOBJ);
            $this->flashMessenger()->addSuccessMessage('LA INFORMACIÓN DE LA CONVOCATORIA FUE ACTUALIZADA');
        } catch (\Exception $ex) {
            $this->logError('ACTUALIZAR CONVOCATORIA - VriconvocatoriasController->editar', $ex);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE!');
        }
        return $this->redirect()->toUrl('index');
    }

    public function actualizararchivoAction()
    {
        $request = $this->getRequest();

        // 1) Leer el id: por query (GET inicial) o por post (envío del form)
        if ($request->isPost()) {
            $id = (int) $this->params()->fromPost('id', 0);
        } else {
            $id = (int) $this->params()->fromQuery('id', 0);
        }

        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE CONVOCATORIA NO VÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        // 2) Cargo el registro completo de la BD
        $infoActual = $this->DAO->getVriconvocatoriaDetalle($id);
        if (!$infoActual) {
            $this->flashMessenger()->addErrorMessage('CONVOCATORIA NO ENCONTRADA');
            return $this->redirect()->toUrl('index');
        }

        // 3) Formulario en modo actualizararchivo (solo File 'documento')
        $form = new VriconvocatoriasForm('actualizararchivo');
        $form->setData($infoActual);

        if ($request->isPost()) {
            // 4) Valido SOLO el archivo
            $files = $request->getFiles()->toArray();
            $documento = $this->procesarArchivo(
                $files,
                'documento',
                ['pdf', 'doc', 'docx'],
                '250B',
                '5MB',
                'CONV',
                null
            );
            if ($documento === false) {
                return $this->redirect()->toUrl('index');
            }
            if (!$documento) {
                $this->flashMessenger()->addErrorMessage('DEBE SELECCIONAR UN ARCHIVO PARA ACTUALIZAR');
                return $this->redirect()->toUrl('index');
            }

            try {
                $infoActual['documento']     = $documento;
                $infoActual['modificadopor'] = $this->getInfoSesion()['login'];
                $infoActual['fechahoramod']  = date('Y-m-d H:i:s');

                $vriconvocatoriasOBJ = new Vriconvocatorias($infoActual);
                $this->DAO->editar($vriconvocatoriasOBJ);

                $this->flashMessenger()->addSuccessMessage('EL DOCUMENTO DE LA CONVOCATORIA FUE ACTUALIZADO');
                return $this->redirect()->toUrl('index');
            } catch (\Exception $ex) {
                $this->logError('ACTUALIZAR ARCHIVO CONVOCATORIA', $ex);
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE!');
            }
        }

        $view = new ViewModel(['form' => $form]);
        $view->setTerminal(true);
        return $view;
    }

    public function desactivarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);
            $info = $this->DAO->getVriconvocatoriaDetalle($id);
            $form = new VriconvocatoriasForm('desactivar');
            $form->setData($info);
            $view = new ViewModel(['form' => $form, 'estado' => 'Eliminado']);
            $view->setTerminal(true);
            return $view;
        }
        $id = (int) $this->params()->fromPost('id', 0);
        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE CONVOCATORIA NO VÁLIDO');
            return $this->redirect()->toUrl('index');
        }
        try {
            $this->DAO->cambiarEstado($id, 'Eliminado');
            $this->flashMessenger()->addSuccessMessage('LA CONVOCATORIA FUE DESACTIVADA EXITOSAMENTE');
        } catch (\Exception $ex) {
            $this->logError('DESACTIVAR CONVOCATORIA', $ex);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! LA CONVOCATORIA NO FUE DESACTIVADA.');
        }
        return $this->redirect()->toUrl('index');
    }

    public function activarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);
            $info = $this->DAO->getVriconvocatoriaDetalle($id);
            $form = new VriconvocatoriasForm('activar');
            $form->setData($info);
            $view = new ViewModel(['form' => $form, 'estado' => 'Activo']);
            $view->setTerminal(true);
            return $view;
        }
        $id = (int) $this->params()->fromPost('id', 0);
        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE CONVOCATORIA NO VÁLIDO');
            return $this->redirect()->toUrl('index');
        }
        try {
            $this->DAO->cambiarEstado($id, 'Activo');
            $this->flashMessenger()->addSuccessMessage('LA CONVOCATORIA FUE ACTIVADA EXITOSAMENTE');
        } catch (\Exception $ex) {
            $this->logError('ACTIVAR CONVOCATORIA', $ex);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! LA CONVOCATORIA NO FUE ACTIVADA.');
        }
        return $this->redirect()->toUrl('index');
    }

    public function eliminarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);
            $info = $this->DAO->getVriconvocatoriaDetalle($id);
            $total = $this->DAO->tieneProyectosAsociados($id);
            $form = new VriconvocatoriasForm('eliminar');
            $form->setData($info);
            $view = new ViewModel([
                'form' => $form,
                'estado' => 'Eliminado',
                'totalAsociados' => $total,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        $id = (int) $this->params()->fromPost('id', 0);
        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE CONVOCATORIA NO VÁLIDO');
            return $this->redirect()->toUrl('index');
        }
        // Bloqueo si tiene proyectos asociados
        $total = $this->DAO->tieneProyectosAsociados($id);
        if ($total > 0) {
            $this->flashMessenger()->addErrorMessage(
                "NO SE PUEDE ELIMINAR: LA CONVOCATORIA TIENE $total PROYECTO(S) ASOCIADO(S). SE SUGIERE DESACTIVARLA."
            );
            return $this->redirect()->toUrl('index');
        }
        try {
            $usOBJ = new Vriconvocatorias(['id' => $id]);
            $this->DAO->eliminar($usOBJ);
            $this->flashMessenger()->addSuccessMessage('LA CONVOCATORIA FUE ELIMINADA');
        } catch (\Exception $ex) {
            $this->logError('ELIMINAR CONVOCATORIA', $ex);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! LA CONVOCATORIA NO FUE ELIMINADA.');
        }
        return $this->redirect()->toUrl('index');
    }

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
                $this->flashMessenger()->addErrorMessage("ARCHIVO DE {$nombreCampo} FUERA DE RANGO DE TAMAÑO ({$tamanoMin} a {$tamanoMax}).");
                return false;
            }
            $extValidator = new \Laminas\Validator\File\Extension(['extension' => $extensionesPermitidas]);
            if (!$extValidator->isValid($files[$nombreCampo])) {
                $exts = implode(', ', $extensionesPermitidas);
                $this->flashMessenger()->addErrorMessage("EXTENSIÓN NO PERMITIDA. PERMITIDOS: {$exts}");
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

    private function logError($contexto, \Exception $ex)
    {
        $msgLog = "\n" . date('Y-m-d H:i:s') . " $contexto \n"
            . $ex->getMessage()
            . "\n----------------------------------------------------------------------- \n";
        $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
        fwrite($file, $msgLog);
        fclose($file);
    }
}
