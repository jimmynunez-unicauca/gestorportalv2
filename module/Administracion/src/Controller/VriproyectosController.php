<?php

declare(strict_types=1);

namespace Administracion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Administracion\Modelo\DAO\VriproyectosDAO;
use Administracion\Modelo\DAO\VriconvocatoriasDAO;
use Administracion\Formularios\VriproyectosForm;
use Administracion\Modelo\Entidades\Vriproyectos;

class VriproyectosController extends AbstractActionController
{
    private $DAO;
    private $convocatoriasDAO;
    private $rutaLog = './public/log/';

    public function __construct(VriproyectosDAO $dao)
    {
        $this->DAO = $dao;
    }

    private function getConvocatoriasDAO()
    {
        if (!$this->convocatoriasDAO) {
            $sm = $this->getEvent()->getApplication()->getServiceManager();
            $this->convocatoriasDAO = $sm->get(VriconvocatoriasDAO::class);
        }
        return $this->convocatoriasDAO;
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

    public function indexAction()
    {
        $filtroConvocatoria = (int) $this->params()->fromQuery('id_convocatoria', 0);
        $filtroEstado = $this->params()->fromQuery('estado_proyecto', '');

        $filtro = [];
        if ($filtroConvocatoria > 0) {
            $filtro[] = "vri_proyectos.id IN (
                SELECT pc.id_proyecto FROM vri_proyectos_convocatorias pc
                WHERE pc.id_convocatoria = $filtroConvocatoria AND pc.estado = 'Activo'
            )";
        }
        if ($filtroEstado !== '') {
            $filtro[] = "vri_proyectos.estado_proyecto = " . $this->DAO->getAdapter()->getPlatform()->quoteValue($filtroEstado);
        }

        $filtroStr = count($filtro) > 0 ? implode(' AND ', $filtro) : '';

        return new ViewModel([
            'fetchAll' => $this->DAO->fetchAll($filtroStr),
            'convocatorias' => $this->getConvocatoriasDAO()->getConvocatoriasActivas(),
            'filtroConvocatoria' => $filtroConvocatoria,
            'filtroEstado' => $filtroEstado,
            'estadosDisponibles' => ['Formulación', 'En Ejecución', 'Aprobado', 'Prórroga', 'Terminado', 'Finalizado'],
        ]);
    }

    public function detalleAction()
    {
        $id = (int) $this->params()->fromQuery('id', 0);
        $detalle = $this->DAO->getVriproyectoDetalle($id);
        $convocatoriasAsociadas = $this->DAO->getConvocatoriasAsociadasDetalle($id);
        $view = new ViewModel([
            'detalle' => $detalle,
            'convocatoriasAsociadas' => $convocatoriasAsociadas,
            'id' => $id,
        ]);
        $view->setTerminal(true);
        return $view;
    }

    public function registrarAction()
    {
        $infosesion    = $this->getInfoSesion();
        $registradopor = $infosesion['login'];

        $convocatoriasDisponibles = $this->getConvocatoriasDAO()->getConvocatoriasActivas();
        $form = new VriproyectosForm('registrar', $convocatoriasDisponibles);

        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }

        // 👇 Debug temporal: quita este bloque cuando funcione
        // echo '<pre>POST: '; print_r($request->getPost()); 
        // echo 'FILES: '; print_r($request->getFiles()); 
        // exit;

        $vriproyectosOBJ = new Vriproyectos();
        $form->setInputFilter($vriproyectosOBJ->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            // 👇 Debug temporal: muestra exactamente qué falla
            // echo '<pre>'; print_r($form->getMessages()); exit;
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }

        // Leer convocatorias (ahora sí viene como 'convocatorias' sin corchetes en getPost)
        $idsConvocatorias = $request->getPost('convocatorias', []);
        if (!is_array($idsConvocatorias)) $idsConvocatorias = [];

        $data = $form->getData();
        // Quitamos 'convocatorias' del data si vino incluido (no es columna de la tabla)
        if (isset($data['convocatorias'])) unset($data['convocatorias']);

        $vriproyectosOBJ->exchangeArray($data);
        $vriproyectosOBJ->setConvocatorias($idsConvocatorias);
        $vriproyectosOBJ->setEstado('Activo');
        $vriproyectosOBJ->setRegistradopor($registradopor);
        $vriproyectosOBJ->setModificadopor('');
        $vriproyectosOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $vriproyectosOBJ->setFechahoramod('0000-00-00 00:00:00');

        try {
            $this->DAO->registrar($vriproyectosOBJ);
            $this->flashMessenger()->addSuccessMessage('EL PROYECTO FUE REGISTRADO EN GESTORPORTALV2');
        } catch (\Exception $ex) {
            $this->logError('REGISTRAR PROYECTO - VriproyectosController->registrar', $ex);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EL PROYECTO NO FUE REGISTRADO.');
        }
        return $this->redirect()->toUrl('index');
    }

    public function editarAction()
    {
        $request = $this->getRequest();
        $convocatoriasDisponibles = $this->getConvocatoriasDAO()->getConvocatoriasActivas();

        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);
            $info = $this->DAO->getVriproyectoDetalle($id);
            if (!$info) {
                $this->flashMessenger()->addErrorMessage('PROYECTO NO ENCONTRADO');
                return $this->redirect()->toUrl('index');
            }
            $form = new VriproyectosForm('editar', $convocatoriasDisponibles);
            $form->setData($info);
            // Preseleccionar convocatorias asociadas
            $idsAsociadas = $this->DAO->getConvocatoriasPorProyecto($id);
            $form->get('convocatorias')->setValue($idsAsociadas);
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }

        $form = new VriproyectosForm('editar', $convocatoriasDisponibles);
        $vriproyectosOBJ = new Vriproyectos();
        $form->setInputFilter($vriproyectosOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            $this->flashMessenger()->addErrorMessage('LA INFORMACIÓN DEL PROYECTO NO ES VÁLIDA');
            return $this->redirect()->toUrl('index');
        }

        $data = $form->getData();
        $idsConvocatorias = $request->getPost('convocatorias', []);
        if (!is_array($idsConvocatorias)) $idsConvocatorias = [];

        try {
            $vriproyectosOBJ->exchangeArray($data);
            $vriproyectosOBJ->setConvocatorias($idsConvocatorias);
            $infosesion = $this->getInfoSesion();
            $vriproyectosOBJ->setModificadopor($infosesion['login']);
            $vriproyectosOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->editar($vriproyectosOBJ);
            $this->flashMessenger()->addSuccessMessage('LA INFORMACIÓN DEL PROYECTO FUE ACTUALIZADA');
        } catch (\Exception $ex) {
            $this->logError('ACTUALIZAR PROYECTO - VriproyectosController->editar', $ex);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE!');
        }
        return $this->redirect()->toUrl('index');
    }

    public function desactivarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);
            $info = $this->DAO->getVriproyectoDetalle($id);
            $form = new VriproyectosForm('desactivar');
            $form->setData($info);
            $view = new ViewModel(['form' => $form, 'estado' => 'Eliminado']);
            $view->setTerminal(true);
            return $view;
        }
        $id = (int) $this->params()->fromPost('id', 0);
        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE PROYECTO NO VÁLIDO');
            return $this->redirect()->toUrl('index');
        }
        try {
            $this->DAO->cambiarEstado($id, 'Eliminado');
            $this->flashMessenger()->addSuccessMessage('EL PROYECTO FUE DESACTIVADO EXITOSAMENTE');
        } catch (\Exception $ex) {
            $this->logError('DESACTIVAR PROYECTO', $ex);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EL PROYECTO NO FUE DESACTIVADO.');
        }
        return $this->redirect()->toUrl('index');
    }

    public function activarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);
            $info = $this->DAO->getVriproyectoDetalle($id);
            $form = new VriproyectosForm('activar');
            $form->setData($info);
            $view = new ViewModel(['form' => $form, 'estado' => 'Activo']);
            $view->setTerminal(true);
            return $view;
        }
        $id = (int) $this->params()->fromPost('id', 0);
        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE PROYECTO NO VÁLIDO');
            return $this->redirect()->toUrl('index');
        }
        try {
            $this->DAO->cambiarEstado($id, 'Activo');
            $this->flashMessenger()->addSuccessMessage('EL PROYECTO FUE ACTIVADO EXITOSAMENTE');
        } catch (\Exception $ex) {
            $this->logError('ACTIVAR PROYECTO', $ex);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EL PROYECTO NO FUE ACTIVADO.');
        }
        return $this->redirect()->toUrl('index');
    }

    public function eliminarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);
            $info = $this->DAO->getVriproyectoDetalle($id);
            $form = new VriproyectosForm('eliminar');
            $form->setData($info);
            $view = new ViewModel(['form' => $form, 'estado' => 'Eliminado']);
            $view->setTerminal(true);
            return $view;
        }
        $id = (int) $this->params()->fromPost('id', 0);
        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE PROYECTO NO VÁLIDO');
            return $this->redirect()->toUrl('index');
        }
        try {
            $usOBJ = new Vriproyectos(['id' => $id]);
            $this->DAO->eliminar($usOBJ);
            $this->flashMessenger()->addSuccessMessage('EL PROYECTO Y SUS RELACIONES FUERON ELIMINADOS');
        } catch (\Exception $ex) {
            $this->logError('ELIMINAR PROYECTO', $ex);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EL PROYECTO NO FUE ELIMINADO.');
        }
        return $this->redirect()->toUrl('index');
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
