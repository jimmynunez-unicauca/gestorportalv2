<?php

declare(strict_types=1);

namespace Administracion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Administracion\Modelo\DAO\EmisoraDAO;
use Administracion\Formularios\ProgramaForm;
use Administracion\Modelo\Entidades\Programa;

class EmisoraController extends AbstractActionController
{
    private $DAO;
    private $rutaLog = './public/log/';
    private $rutaArchivos = '/var/www/html/newportal/archivos/emisora/';
    //private $rutaArchivos = './../newportal/archivos/emisora/';

    public function __construct(EmisoraDAO $dao)
    {
        $this->DAO = $dao;
    }

    private function getInfoSesion()
    {
        $infoSesion = ['login' => 'SIN INICIO DE SESION'];
        $auth = new AuthenticationService();
        if ($auth->hasIdentity()) {
            $infoSesion['login'] = $auth->getIdentity()->login;
        }
        return $infoSesion;
    }

    private function registrarLogError($accion, $excepcion)
    {
        $msgLog = "\n" . date('Y-m-d H:i:s') . " {$accion} - EmisoraController \n"
            . $excepcion->getMessage()
            . "\n----------------------------------------------------------------------- \n";
        file_put_contents($this->rutaLog . 'gestorportal.log', $msgLog, FILE_APPEND);
    }

    private function procesarArchivo($files, $nombreCampo, $extensiones, $tamanoMin, $tamanoMax, $directorio, $default)
    {
        if (!isset($files[$nombreCampo]) || $files[$nombreCampo]['error'] === UPLOAD_ERR_NO_FILE) {
            return $default;
        }

        $uploadOK = new \Laminas\Validator\File\UploadFile();
        if (!$uploadOK->isValid($files[$nombreCampo])) {
            $this->flashMessenger()->addErrorMessage("ERROR AL CARGAR EL ARCHIVO DE {$nombreCampo}");
            return false;
        }

        $sizeValidator = new \Laminas\Validator\File\Size(['min' => $tamanoMin, 'max' => $tamanoMax]);
        if (!$sizeValidator->isValid($files[$nombreCampo])) {
            $this->flashMessenger()->addErrorMessage("ARCHIVO FUERA DE RANGO");
            return false;
        }

        $extValidator = new \Laminas\Validator\File\Extension(['extension' => $extensiones]);
        if (!$extValidator->isValid($files[$nombreCampo])) {
            $this->flashMessenger()->addErrorMessage("EXTENSIÓN NO PERMITIDA");
            return false;
        }

        $ext = pathinfo($files[$nombreCampo]['name'], PATHINFO_EXTENSION);
        $filter = new \Laminas\Filter\File\RenameUpload([
            'target' => $this->rutaArchivos . "{$directorio}_" . time() . '.' . $ext,
            'randomize' => true,
        ]);
        $upload = $filter->filter($files[$nombreCampo]);

        if ($upload['error'] != 0) {
            $this->flashMessenger()->addErrorMessage("NO FUE POSIBLE SUBIR EL ARCHIVO");
            return false;
        }
        return basename($upload['tmp_name']);
    }

    //==========================================================================
    public function indexAction()
    {
        return new ViewModel([
            'fetchAll' => $this->DAO->fetchAll(''),
        ]);
    }

    //==========================================================================
    public function registrarAction()
    {
        $infosesion = $this->getInfoSesion();
        $form = new ProgramaForm('registrar');
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $programaOBJ = new Programa();
        $form->setInputFilter($programaOBJ->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            $this->flashMessenger()->addErrorMessage(
                'INFORMACIÓN NO VÁLIDA: ' . json_encode($form->getMessages(), JSON_UNESCAPED_UNICODE)
            );
            return $this->redirect()->toUrl('index');
        }

        $files = $request->getFiles()->toArray();
        $respaldoImagen = $this->procesarArchivo(
            $files,
            'imagen',
            ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'],
            '250B',
            '500KB',
            'programas/PROGRAMA',
            'sinImagen.png'
        );
        if ($respaldoImagen === false) return $this->redirect()->toUrl('index');

        $programaOBJ->exchangeArray($form->getData());
        $programaOBJ->setImagen($respaldoImagen);
        $programaOBJ->setEstado('Activo');
        $programaOBJ->setRegistradopor($infosesion['login']);
        $programaOBJ->setModificadopor('');
        $programaOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $programaOBJ->setFechahoramod('0000-00-00 00:00:00');

        try {
            $this->DAO->registrar($programaOBJ);
            $this->flashMessenger()->addSuccessMessage('EL PROGRAMA FUE REGISTRADO');
        } catch (\Exception $ex) {
            $this->registrarLogError('REGISTRAR PROGRAMA', $ex);
            $this->flashMessenger()->addErrorMessage('EL PROGRAMA NO FUE REGISTRADO');
        }
        return $this->redirect()->toUrl('index');
    }

    //==========================================================================
    public function editarAction()
    {
        $request = $this->getRequest();
        $idPrograma = $request->isPost()
            ? (int) $request->getPost('idPrograma', 0)
            : (int) $this->params()->fromQuery('idPrograma', 0);

        if ($idPrograma <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE PROGRAMA INVÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        $programaOBJ = $this->DAO->getPrograma($idPrograma);
        if (!$programaOBJ) {
            $this->flashMessenger()->addErrorMessage('EL PROGRAMA NO EXISTE');
            return $this->redirect()->toUrl('index');
        }

        $form = new ProgramaForm('editar');
        $form->bind($programaOBJ);

        if (!$request->isPost()) {
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $form->setData($request->getPost());
        if (!$form->isValid()) {
            $this->flashMessenger()->addErrorMessage(
                'INFORMACIÓN NO VÁLIDA: ' . json_encode($form->getMessages(), JSON_UNESCAPED_UNICODE)
            );
            return $this->redirect()->toUrl('index');
        }

        $session = $this->getInfoSesion();
        $programaOBJ->setModificadopor($session['login']);
        $programaOBJ->setFechahoramod(date('Y-m-d H:i:s'));

        try {
            $this->DAO->editar($programaOBJ);
            $this->flashMessenger()->addSuccessMessage('EL PROGRAMA FUE EDITADO');
        } catch (\Exception $ex) {
            $this->registrarLogError('EDITAR PROGRAMA', $ex);
            $this->flashMessenger()->addErrorMessage('EL PROGRAMA NO FUE EDITADO');
        }
        return $this->redirect()->toUrl('index');
    }

    //==========================================================================
    public function detalleAction()
    {
        $idPrograma = (int) $this->params()->fromQuery('idPrograma', 0);
        $info = $this->DAO->getProgramaDetalle($idPrograma);
        if (!$info) {
            $this->flashMessenger()->addErrorMessage('EL PROGRAMA NO EXISTE');
            return $this->redirect()->toUrl('index');
        }
        return (new ViewModel(['form' => $info]))->setTerminal(true);
    }

    //==========================================================================
    // DESACTIVAR (soft delete → estado = 'Eliminado')
    //==========================================================================
    public function desactivarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $idPrograma = (int) $this->params()->fromQuery('idPrograma', 0);
            $info = $this->DAO->getProgramaDetalle($idPrograma);
            if (!$info) {
                $this->flashMessenger()->addErrorMessage('EL PROGRAMA NO EXISTE');
                return $this->redirect()->toUrl('index');
            }
            $form = new ProgramaForm('desactivar');
            $form->setData($info);
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $idPrograma = (int) $request->getPost('idPrograma', 0);
        if ($idPrograma <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE PROGRAMA INVÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        try {
            $session = $this->getInfoSesion();
            $programaOBJ = new Programa();
            $programaOBJ->setIdPrograma($idPrograma);
            $programaOBJ->setModificadopor($session['login']);
            $programaOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->eliminarActivar($programaOBJ, 'Eliminado');
            $this->flashMessenger()->addSuccessMessage('EL PROGRAMA FUE DESACTIVADO');
        } catch (\Exception $ex) {
            $this->registrarLogError('DESACTIVAR PROGRAMA', $ex);
            $this->flashMessenger()->addErrorMessage('EL PROGRAMA NO FUE DESACTIVADO');
        }
        return $this->redirect()->toUrl('index');
    }

    //==========================================================================
    // ACTIVAR (soft delete → estado = 'Activo')
    //==========================================================================
    public function activarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $idPrograma = (int) $this->params()->fromQuery('idPrograma', 0);
            $info = $this->DAO->getProgramaDetalle($idPrograma);
            if (!$info) {
                $this->flashMessenger()->addErrorMessage('EL PROGRAMA NO EXISTE');
                return $this->redirect()->toUrl('index');
            }
            $form = new ProgramaForm('activar');
            $form->setData($info);
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $idPrograma = (int) $request->getPost('idPrograma', 0);
        if ($idPrograma <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE PROGRAMA INVÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        try {
            $session = $this->getInfoSesion();
            $programaOBJ = new Programa();
            $programaOBJ->setIdPrograma($idPrograma);
            $programaOBJ->setModificadopor($session['login']);
            $programaOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->eliminarActivar($programaOBJ, 'Activo');
            $this->flashMessenger()->addSuccessMessage('EL PROGRAMA FUE ACTIVADO');
        } catch (\Exception $ex) {
            $this->registrarLogError('ACTIVAR PROGRAMA', $ex);
            $this->flashMessenger()->addErrorMessage('EL PROGRAMA NO FUE ACTIVADO');
        }
        return $this->redirect()->toUrl('index');
    }

    //==========================================================================
    // ACTUALIZAR IMAGEN (modal auxiliar)
    //==========================================================================
    public function actualizarimagenAction()
    {
        $request = $this->getRequest();
        $idPrograma = $request->isPost()
            ? (int) $request->getPost('idPrograma', 0)
            : (int) $this->params()->fromQuery('idPrograma', 0);

        $programaOBJ = $this->DAO->getPrograma($idPrograma);
        if (!$programaOBJ) {
            $this->flashMessenger()->addErrorMessage('EL PROGRAMA NO EXISTE');
            return $this->redirect()->toUrl('index');
        }

        $form = new ProgramaForm('actualizarimagen');
        $form->bind($programaOBJ);

        if (!$request->isPost()) {
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $files = $request->getFiles()->toArray();
        $respaldoImagen = $this->procesarArchivo(
            $files,
            'imagen',
            ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'],
            '250B',
            '500KB',
            'programas/PROGRAMA',
            null
        );

        if ($respaldoImagen === false) {
            return $this->redirect()->toUrl('index');
        }
        if ($respaldoImagen === null) {
            $this->flashMessenger()->addErrorMessage('DEBE SELECCIONAR UNA IMAGEN');
            return $this->redirect()->toUrl('index');
        }

        try {
            $session = $this->getInfoSesion();
            $programaOBJ->setImagen($respaldoImagen);
            $programaOBJ->setModificadopor($session['login']);
            $programaOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->editar($programaOBJ);
            $this->flashMessenger()->addSuccessMessage('LA IMAGEN FUE ACTUALIZADA');
        } catch (\Exception $ex) {
            $this->registrarLogError('ACTUALIZAR IMAGEN', $ex);
            $this->flashMessenger()->addErrorMessage('LA IMAGEN NO FUE ACTUALIZADA');
        }
        return $this->redirect()->toUrl('index');
    }

    //==========================================================================
    // ELIMINAR DEFINITIVAMENTE (hard delete)
    // Solo si no tiene programación asociada
    //==========================================================================
    public function eliminarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $idPrograma = (int) $this->params()->fromQuery('idPrograma', 0);
            $info = $this->DAO->getProgramaDetalle($idPrograma);
            if (!$info) {
                $this->flashMessenger()->addErrorMessage('EL PROGRAMA NO EXISTE');
                return $this->redirect()->toUrl('index');
            }
            $form = new ProgramaForm('eliminar');
            $form->setData($info);
            return (new ViewModel([
                'form'              => $form,
                'tieneProgramacion' => $this->DAO->tieneProgramacion($idPrograma),
            ]))->setTerminal(true);
        }

        $idPrograma = (int) $request->getPost('idPrograma', 0);
        if ($idPrograma <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE PROGRAMA INVÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        if ($this->DAO->tieneProgramacion($idPrograma)) {
            $this->flashMessenger()->addErrorMessage(
                'NO SE PUEDE BORRAR: EL PROGRAMA TIENE PROGRAMACIÓN ASOCIADA.'
            );
            return $this->redirect()->toUrl('index');
        }

        try {
            $this->DAO->borrarPrograma($idPrograma);
            $this->flashMessenger()->addSuccessMessage('EL PROGRAMA FUE ELIMINADO DEFINITIVAMENTE');
        } catch (\Exception $ex) {
            $this->registrarLogError('ELIMINAR PROGRAMA', $ex);
            $this->flashMessenger()->addErrorMessage('EL PROGRAMA NO FUE ELIMINADO');
        }
        return $this->redirect()->toUrl('index');
    }
}
