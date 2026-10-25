<?php

declare(strict_types=1);

namespace Administracion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Administracion\Modelo\DAO\EmisoraDAO;
use Administracion\Formularios\ProgramacionForm;
use Administracion\Modelo\Entidades\Programacion;

class EmisoraProgramacionController extends AbstractActionController
{
    private $DAO;
    private $rutaLog = './public/log/';

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

    //--------------------------------------------------------------------------
    // LISTADO + CALENDARIO
    //--------------------------------------------------------------------------
    public function indexAction()
    {
        return new ViewModel([
            'fetchAll' => $this->DAO->fetchAllProgramacion(''),
            'urlCalendar' => $this->url()->fromRoute('administracion/emisora-programacion', ['action' => 'calendar']),
        ]);
    }

    //--------------------------------------------------------------------------
    // ENDPOINT JSON PARA FULLCALENDAR
    //--------------------------------------------------------------------------
    public function calendarAction()
    {
        $start = $this->params()->fromQuery('start', date('Y-m-d'));
        $end   = $this->params()->fromQuery('end', date('Y-m-d', strtotime('+1 month')));

        try {
            $startDate = new \DateTime($start);
            $endDate   = new \DateTime($end);
        } catch (\Exception $e) {
            return new JsonModel([]);
        }

        $eventos = $this->DAO->getEventosCalendario($startDate, $endDate);
        return new JsonModel($eventos);
    }

    //--------------------------------------------------------------------------
    // REGISTRAR
    //--------------------------------------------------------------------------
    public function registrarAction()
    {
        $infosesion = $this->getInfoSesion();
        $form = new ProgramacionForm('registrar');
        // Poblar el select de programas
        $form->get('idPrograma')->setValueOptions($this->getOpcionesProgramas());

        $request = $this->getRequest();
        if (!$request->isPost()) {
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $obj = new Programacion();
        $form->setInputFilter($obj->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            $this->flashMessenger()->addErrorMessage('INFORMACIÓN NO VÁLIDA: ' . json_encode($form->getMessages()));
            return $this->redirect()->toUrl('index');
        }

        $data = $form->getData();
        $dias = $data['dias'] ?? [];
        unset($data['dias']);

        $obj->exchangeArray($data);
        if ($obj->getVigente_hasta() === '' || $obj->getVigente_hasta() === null) {
            $obj->setVigente_hasta(null);
        }
        $obj->setDias_semana(implode(',', $dias));
        $obj->setEstado('Activo');
        $obj->setRegistradopor($infosesion['login']);
        $obj->setFechahorareg(date('Y-m-d H:i:s'));

        try {
            $this->DAO->registrarProgramacion($obj);
            $this->flashMessenger()->addSuccessMessage('LA PROGRAMACIÓN FUE REGISTRADA');
        } catch (\Exception $ex) {
            $this->registrarLogError('REGISTRAR PROGRAMACIÓN', $ex);
            $this->flashMessenger()->addErrorMessage('LA PROGRAMACIÓN NO FUE REGISTRADA');
        }
        return $this->redirect()->toUrl('index');
    }

    //--------------------------------------------------------------------------
    // EDITAR
    //--------------------------------------------------------------------------
    public function editarAction()
    {
        $request = $this->getRequest();

        // Si es POST, el id viene en el body del formulario.
        // Si es GET (para cargar el modal), viene en la query string.
        $id = $request->isPost()
            ? (int) $request->getPost('idProgramacion', 0)
            : (int) $this->params()->fromQuery('idProgramacion', 0);

        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE PROGRAMACIÓN INVÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        $obj = $this->DAO->getProgramacion($id);
        if (!$obj) {
            $this->flashMessenger()->addErrorMessage('LA PROGRAMACIÓN NO EXISTE');
            return $this->redirect()->toUrl('index');
        }

        $form = new ProgramacionForm('editar');
        $form->get('idPrograma')->setValueOptions($this->getOpcionesProgramas());
        $form->get('dias')->setValue(explode(',', $obj->getDias_semana()));
        $form->bind($obj);

        if (!$request->isPost()) {
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        // Capturamos los días del POST ANTES de setData, porque 'dias'
        // no es una propiedad de la entidad y no se hidrata con bind().
        $post = $request->getPost()->toArray();
        $dias = $post['dias'] ?? [];

        $form->setData($post);
        if (!$form->isValid()) {
            $this->flashMessenger()->addErrorMessage(
                'INFORMACIÓN NO VÁLIDA: ' . json_encode($form->getMessages(), JSON_UNESCAPED_UNICODE)
            );
            return $this->redirect()->toUrl('index');
        }

        // Después de isValid(), $obj ya tiene los datos del POST.
        // Solo actualizamos días y auditoría manualmente.
        $session = $this->getInfoSesion();
        $obj->setDias_semana(implode(',', $dias));
        if ($obj->getVigente_hasta() === '' || $obj->getVigente_hasta() === null) {
            $obj->setVigente_hasta(null);
        }
        $obj->setModificadopor($session['login']);
        $obj->setFechahoramod(date('Y-m-d H:i:s'));

        try {
            $this->DAO->editarProgramacion($obj);
            $this->flashMessenger()->addSuccessMessage('LA PROGRAMACIÓN FUE EDITADA');
        } catch (\Exception $ex) {
            $this->registrarLogError('EDITAR PROGRAMACIÓN', $ex);
            $this->flashMessenger()->addErrorMessage('LA PROGRAMACIÓN NO FUE EDITADA: ' . $ex->getMessage());
        }
        return $this->redirect()->toUrl('index');
    }

    //--------------------------------------------------------------------------
    // DETALLE
    //--------------------------------------------------------------------------
    public function detalleAction()
    {
        $id = (int) $this->params()->fromQuery('idProgramacion', 0);
        $info = $this->DAO->getProgramacionDetalle($id);
        return (new ViewModel(['form' => $info]))->setTerminal(true);
    }

    //==========================================================================
    // ACTIVAR (soft → estado = 'Activo')
    //==========================================================================
    public function activarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('idProgramacion', 0);
            $info = $this->DAO->getProgramacionDetalle($id);
            if (!$info) {
                $this->flashMessenger()->addErrorMessage('LA PROGRAMACIÓN NO EXISTE');
                return $this->redirect()->toUrl('index');
            }
            $form = new ProgramacionForm('activar');
            $form->get('idPrograma')->setValueOptions($this->getOpcionesProgramas());
            $form->get('dias')->setValue(explode(',', $info['dias_semana']));
            $form->setData($info);
            return (new ViewModel([
                'form' => $form,
                'info' => $info,
            ]))->setTerminal(true);
        }

        $id = (int) $request->getPost('idProgramacion', 0);
        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE PROGRAMACIÓN INVÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        try {
            $session = $this->getInfoSesion();
            $obj = new Programacion();
            $obj->setIdProgramacion($id);
            $obj->setModificadopor($session['login']);
            $obj->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->eliminarActivarProgramacion($obj, 'Activo');
            $this->flashMessenger()->addSuccessMessage('LA PROGRAMACIÓN FUE ACTIVADA');
        } catch (\Exception $ex) {
            $this->registrarLogError('ACTIVAR PROGRAMACIÓN', $ex);
            $this->flashMessenger()->addErrorMessage('LA PROGRAMACIÓN NO FUE ACTIVADA: ' . $ex->getMessage());
        }
        return $this->redirect()->toUrl('index');
    }

    //==========================================================================
    // DESACTIVAR (soft → estado = 'Eliminado')
    //==========================================================================
    public function desactivarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('idProgramacion', 0);
            $info = $this->DAO->getProgramacionDetalle($id);
            if (!$info) {
                $this->flashMessenger()->addErrorMessage('LA PROGRAMACIÓN NO EXISTE');
                return $this->redirect()->toUrl('index');
            }
            $form = new ProgramacionForm('desactivar');
            $form->get('idPrograma')->setValueOptions($this->getOpcionesProgramas());
            $form->get('dias')->setValue(explode(',', $info['dias_semana']));
            $form->setData($info);
            return (new ViewModel([
                'form' => $form,
                'info' => $info,
            ]))->setTerminal(true);
        }

        $id = (int) $request->getPost('idProgramacion', 0);
        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE PROGRAMACIÓN INVÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        try {
            $session = $this->getInfoSesion();
            $obj = new Programacion();
            $obj->setIdProgramacion($id);
            $obj->setModificadopor($session['login']);
            $obj->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->eliminarActivarProgramacion($obj, 'Eliminado');
            $this->flashMessenger()->addSuccessMessage('LA PROGRAMACIÓN FUE DESACTIVADA');
        } catch (\Exception $ex) {
            $this->registrarLogError('DESACTIVAR PROGRAMACIÓN', $ex);
            $this->flashMessenger()->addErrorMessage('LA PROGRAMACIÓN NO FUE DESACTIVADA: ' . $ex->getMessage());
        }
        return $this->redirect()->toUrl('index');
    }

    //==========================================================================
    // ELIMINAR (hard → DELETE de la tabla)
    // Solo si no tiene excepciones asociadas
    //==========================================================================
    public function eliminarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('idProgramacion', 0);
            $info = $this->DAO->getProgramacionDetalle($id);
            if (!$info) {
                $this->flashMessenger()->addErrorMessage('LA PROGRAMACIÓN NO EXISTE');
                return $this->redirect()->toUrl('index');
            }
            $form = new ProgramacionForm('eliminar');
            $form->get('idPrograma')->setValueOptions($this->getOpcionesProgramas());
            $form->get('dias')->setValue(explode(',', $info['dias_semana']));
            $form->setData($info);
            return (new ViewModel([
                'form'             => $form,
                'info' => $info,
                'tieneExcepciones' => $this->DAO->tieneExcepciones($id),
            ]))->setTerminal(true);
        }

        $id = (int) $request->getPost('idProgramacion', 0);
        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE PROGRAMACIÓN INVÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        if ($this->DAO->tieneExcepciones($id)) {
            $this->flashMessenger()->addErrorMessage(
                'NO SE PUEDE ELIMINAR: LA PROGRAMACIÓN TIENE EXCEPCIONES ASOCIADAS.'
            );
            return $this->redirect()->toUrl('index');
        }

        try {
            $this->DAO->borrarProgramacion($id);
            $this->flashMessenger()->addSuccessMessage('LA PROGRAMACIÓN FUE ELIMINADA DEFINITIVAMENTE');
        } catch (\Exception $ex) {
            $this->registrarLogError('ELIMINAR PROGRAMACIÓN', $ex);
            $this->flashMessenger()->addErrorMessage('LA PROGRAMACIÓN NO FUE ELIMINADA: ' . $ex->getMessage());
        }
        return $this->redirect()->toUrl('index');
    }

    public function actualizarhorarioAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            return new JsonModel(['success' => false, 'message' => 'Método no permitido']);
        }

        $id = (int) $request->getPost('idProgramacion', 0);
        $nuevoInicio = $request->getPost('nuevoInicio', '');
        $nuevoFin = $request->getPost('nuevoFin', '');

        if ($id <= 0 || empty($nuevoInicio)) {
            return new JsonModel(['success' => false, 'message' => 'Datos incompletos']);
        }

        $obj = $this->DAO->getProgramacion($id);
        if (!$obj) {
            return new JsonModel(['success' => false, 'message' => 'La programación no existe']);
        }

        // Extraer día de la semana y horas del nuevo inicio
        $dtInicio = new \DateTime($nuevoInicio);
        $diaSemana = strtolower($dtInicio->format('l')); // 'monday', 'tuesday', etc.
        $mapDias = [
            'monday' => 'lunes',
            'tuesday' => 'martes',
            'wednesday' => 'miércoles',
            'thursday' => 'jueves',
            'friday' => 'viernes',
            'saturday' => 'sábado',
            'sunday' => 'domingo'
        ];
        $diaSemana = $mapDias[$diaSemana] ?? $obj->getDias_semana();

        $horaInicio = $dtInicio->format('H:i:s');
        $horaFin = !empty($nuevoFin) ? (new \DateTime($nuevoFin))->format('H:i:s') : $obj->getHora_fin();

        // Actualizar la entidad
        $obj->setDias_semana($diaSemana);
        $obj->setHora_inicio($horaInicio);
        $obj->setHora_fin($horaFin);
        $obj->setModificadopor($this->getInfoSesion()['login']);
        $obj->setFechahoramod(date('Y-m-d H:i:s'));

        try {
            $this->DAO->editarProgramacion($obj);
            return new JsonModel(['success' => true, 'message' => 'Horario actualizado']);
        } catch (\Exception $ex) {
            $this->registrarLogError('ACTUALIZAR HORARIO', $ex);
            return new JsonModel(['success' => false, 'message' => 'Error al actualizar: ' . $ex->getMessage()]);
        }
    }

    //--------------------------------------------------------------------------
    // HELPERS
    //--------------------------------------------------------------------------
    private function getOpcionesProgramas(): array
    {
        $opciones = ['' => 'Seleccione...'];
        foreach ($this->DAO->getProgramasActivos() as $p) {
            $opciones[$p['idPrograma']] = $p['nombre_programa'];
        }
        return $opciones;
    }

    private function registrarLogError($accion, $excepcion)
    {
        $msgLog = "\n" . date('Y-m-d H:i:s') . " {$accion} - EmisoraProgramacionController \n"
            . $excepcion->getMessage()
            . "\n----------------------------------------------------------------------- \n";
        file_put_contents($this->rutaLog . 'gestorportal.log', $msgLog, FILE_APPEND);
    }
}
