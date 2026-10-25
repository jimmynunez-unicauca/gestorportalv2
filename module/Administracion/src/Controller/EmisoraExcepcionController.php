<?php

namespace Administracion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Administracion\Modelo\DAO\EmisoraDAO;
use Administracion\Formularios\ExcepcionForm;
use Administracion\Modelo\Entidades\Excepcion;

class EmisoraExcepcionController extends AbstractActionController
{
    private $DAO;
    private $rutaLog = './public/log/';

    public function __construct(EmisoraDAO $dao)
    {
        $this->DAO = $dao;
    }

    private function getInfoSesion()
    {
        $info = ['login' => 'SIN INICIO DE SESION'];
        $auth = new AuthenticationService();
        if ($auth->hasIdentity()) $info['login'] = $auth->getIdentity()->login;
        return $info;
    }

    public function indexAction()
    {
        return new ViewModel(['fetchAll' => $this->DAO->fetchAllExcepciones('')]);
    }

    public function registrarAction()
    {
        $form = new ExcepcionForm('registrar');
        $form->get('idProgramacion_original')->setValueOptions($this->getOpcionesProgramaciones());
        $form->get('idPrograma_reemplazo')->setValueOptions($this->getOpcionesProgramas());

        $request = $this->getRequest();
        if (!$request->isPost()) {
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $post = $request->getPost()->toArray();

        // Validación condicional según el tipo
        $tipo = $post['tipo'] ?? '';
        $errores = [];

        if ($tipo === 'cancelacion') {
            if (empty($post['idProgramacion_original'])) {
                $errores[] = 'Debe seleccionar la programación afectada.';
            }
        }

        if ($tipo === 'cancelacion_dia') {
            if (empty($post['fecha'])) {
                $errores[] = 'Debe indicar la fecha del día completo a cancelar.';
            }
        }

        if ($tipo === 'reemplazo') {
            if (empty($post['idPrograma_reemplazo'])) $errores[] = 'Debe seleccionar el programa de reemplazo.';
            if (empty($post['hora_inicio'])) $errores[] = 'Debe indicar la hora de inicio.';
            if (empty($post['hora_fin']))    $errores[] = 'Debe indicar la hora de fin.';
        }

        if ($tipo === 'ajuste_horario') {
            if (empty($post['idProgramacion_original'])) $errores[] = 'Debe seleccionar la programación afectada.';
            if (empty($post['hora_inicio'])) $errores[] = 'Debe indicar la nueva hora de inicio.';
            if (empty($post['hora_fin']))    $errores[] = 'Debe indicar la nueva hora de fin.';
        }

        if (!empty($errores)) {
            $this->flashMessenger()->addErrorMessage(implode(' ', $errores));
            return $this->redirect()->toRoute('administracion/emisora-excepcion', ['action' => 'index']);
        }

        // Construir el objeto directamente desde el POST (sin InputFilter)
        $obj = new Excepcion();
        $obj->setFecha($post['fecha'] ?? null);
        $obj->setTipo($post['tipo'] ?? null);
        $obj->setIdProgramacion_original(!empty($post['idProgramacion_original']) ? (int) $post['idProgramacion_original'] : null);
        $obj->setIdPrograma_reemplazo(!empty($post['idPrograma_reemplazo']) ? (int) $post['idPrograma_reemplazo'] : null);
        $obj->setHora_inicio(!empty($post['hora_inicio']) ? $post['hora_inicio'] : null);
        $obj->setHora_fin(!empty($post['hora_fin']) ? $post['hora_fin'] : null);
        $obj->setMotivo($post['motivo'] ?? null);
        $obj->setRegistradopor($this->getInfoSesion()['login']);
        $obj->setFechahorareg(date('Y-m-d H:i:s'));

        try {
            $this->DAO->registrarExcepcion($obj);
            $this->flashMessenger()->addSuccessMessage('LA EXCEPCIÓN FUE REGISTRADA');
        } catch (\Exception $ex) {
            error_log('ERROR INSERT EXCEPCION: ' . $ex->getMessage());
            $this->flashMessenger()->addErrorMessage('LA EXCEPCIÓN NO FUE REGISTRADA: ' . $ex->getMessage());
        }
        return $this->redirect()->toRoute('administracion/emisora-excepcion', ['action' => 'index']);
    }

    public function editarAction()
    {
        $request = $this->getRequest();

        // Si es POST, el id viene en el body del formulario.
        // Si es GET (para cargar el modal), viene en la query string.
        $id = $request->isPost()
            ? (int) $request->getPost('idExcepcion', 0)
            : (int) $this->params()->fromQuery('idExcepcion', 0);

        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE EXCEPCIÓN INVÁLIDO');
            return $this->redirect()->toRoute('administracion/emisora-excepcion', ['action' => 'index']);
        }

        $obj = $this->DAO->getExcepcion($id);
        if (!$obj) {
            $this->flashMessenger()->addErrorMessage('LA EXCEPCIÓN NO EXISTE');
            return $this->redirect()->toRoute('administracion/emisora-excepcion', ['action' => 'index']);
        }

        $form = new ExcepcionForm('editar');
        $form->get('idProgramacion_original')->setValueOptions($this->getOpcionesProgramaciones());
        $form->get('idPrograma_reemplazo')->setValueOptions($this->getOpcionesProgramas());
        $form->bind($obj);

        if (!$request->isPost()) {
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $post = $request->getPost()->toArray();

        $obj->setFecha($post['fecha'] ?? null);
        $obj->setTipo($post['tipo'] ?? null);
        $obj->setIdProgramacion_original(!empty($post['idProgramacion_original']) ? (int) $post['idProgramacion_original'] : null);
        $obj->setIdPrograma_reemplazo(!empty($post['idPrograma_reemplazo']) ? (int) $post['idPrograma_reemplazo'] : null);
        $obj->setHora_inicio(!empty($post['hora_inicio']) ? $post['hora_inicio'] : null);
        $obj->setHora_fin(!empty($post['hora_fin']) ? $post['hora_fin'] : null);
        $obj->setMotivo($post['motivo'] ?? null);

        try {
            $this->DAO->editarExcepcion($obj);
            $this->flashMessenger()->addSuccessMessage('LA EXCEPCIÓN FUE EDITADA');
        } catch (\Exception $ex) {
            error_log('ERROR UPDATE EXCEPCION: ' . $ex->getMessage());
            $this->flashMessenger()->addErrorMessage('LA EXCEPCIÓN NO FUE EDITADA: ' . $ex->getMessage());
        }
        return $this->redirect()->toRoute('administracion/emisora-excepcion', ['action' => 'index']);
    }

    public function detalleAction()
    {
        $id = (int) $this->params()->fromQuery('idExcepcion', 0);
        $info = $this->DAO->getExcepcionDetalle($id);
        return (new ViewModel(['form' => $info]))->setTerminal(true);
    }

    public function eliminarAction()
    {
        $request = $this->getRequest();

        // Si es POST, el id viene en el body del formulario.
        // Si es GET (para cargar el modal), viene en la query string.
        $id = $request->isPost()
            ? (int) $request->getPost('idExcepcion', 0)
            : (int) $this->params()->fromQuery('idExcepcion', 0);

        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE EXCEPCIÓN INVÁLIDO');
            return $this->redirect()->toRoute('administracion/emisora-excepcion', ['action' => 'index']);
        }

        $info = $this->DAO->getExcepcionDetalle($id);
        if (!$info) {
            $this->flashMessenger()->addErrorMessage('LA EXCEPCIÓN NO EXISTE');
            return $this->redirect()->toRoute('administracion/emisora-excepcion', ['action' => 'index']);
        }

        if (!$request->isPost()) {
            $form = new ExcepcionForm('eliminar');
            $form->setData($info);
            return (new ViewModel([
                'form' => $form,
                'info' => $info,
            ]))->setTerminal(true);
        }

        try {
            error_log('Intentando eliminar excepcion ID: ' . $id);
            $this->DAO->eliminarExcepcion($id);
            error_log('Excepcion eliminada ID: ' . $id);
            $this->flashMessenger()->addSuccessMessage('LA EXCEPCIÓN FUE ELIMINADA');
        } catch (\Exception $ex) {
            error_log('ERROR DELETE EXCEPCION: ' . $ex->getMessage());
            $this->flashMessenger()->addErrorMessage('LA EXCEPCIÓN NO FUE ELIMINADA: ' . $ex->getMessage());
        }
        return $this->redirect()->toRoute('administracion/emisora-excepcion', ['action' => 'index']);
    }

    private function getOpcionesProgramaciones(): array
    {
        $opciones = ['' => 'Seleccione...'];
        foreach ($this->DAO->getProgramacionesSelect() as $p) {
            $opciones[$p['idProgramacion']] = "{$p['nombre_programa']} ({$p['dias_semana']} {$p['hora_inicio']}-{$p['hora_fin']})";
        }
        return $opciones;
    }

    private function getOpcionesProgramas(): array
    {
        $opciones = ['' => 'Seleccione...'];
        foreach ($this->DAO->getProgramasActivos() as $p) {
            $opciones[$p['idPrograma']] = $p['nombre_programa'];
        }
        return $opciones;
    }
}
