<?php

declare(strict_types=1);

namespace Formularios\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Formularios\Modelo\DAO\ConvocatoriaDAO;
use Formularios\Modelo\DAO\PfiDAO;
use Formularios\Formularios\ConvocatoriaForm;
use Formularios\Modelo\Entidades\Convocatoria;

class ConvocatoriaController extends AbstractActionController
{
    private $DAO;
    private $pfiDAO;
    private $rutaLog = './public/log/';

    public function __construct(ConvocatoriaDAO $dao, PfiDAO $pfiDAO)
    {
        $this->DAO = $dao;
        $this->pfiDAO = $pfiDAO;
    }

    private function getInfoSesion()
    {
        $auth = new AuthenticationService();
        if ($auth->hasIdentity()) {
            return [
                'login' => $auth->getIdentity()->login,
                'idEmpleadoCliente' => $auth->getIdentity()->idEmpleadoCliente,
            ];
        }
        return [
            'login' => 'SIN INICIO DE SESION',
            'idEmpleadoCliente' => 0,
        ];
    }

    private function getConfiguracionesOptions()
    {
        try {
            $configs = $this->pfiDAO->fetchAll();

            // Verificar que $configs no sea null
            if ($configs === null) {
                $configs = [];
            }

            $options = [];
            foreach ($configs as $config) {
                if (isset($config['id_config']) && isset($config['nombre_formulario'])) {
                    $options[$config['id_config']] = $config['nombre_formulario'];
                }
            }

            return $options;
        } catch (\Exception $e) {
            error_log("ERROR en getConfiguracionesOptions: " . $e->getMessage());
            return [];
        }
    }

    private function getFormulario($action = '', $idConvocatoria = 0)
    {
        $configOptions = $this->getConfiguracionesOptions();
        $form = new ConvocatoriaForm($action, $configOptions);

        if ($idConvocatoria != 0) {
            $convocatoriaOBJ = $this->DAO->getConvocatoria($idConvocatoria);
            if ($convocatoriaOBJ) {
                // Preparar datos para el formulario
                $data = [
                    'id_convocatoria' => $convocatoriaOBJ->getIdConvocatoria(),
                    'id_config' => $convocatoriaOBJ->getIdConfig(),
                    'nombre_convocatoria' => $convocatoriaOBJ->getNombreConvocatoria(),
                    'periodo' => $convocatoriaOBJ->getPeriodo(),
                    'cupo_maximo' => $convocatoriaOBJ->getCupoMaximo(),
                    'inscritos_actuales' => $convocatoriaOBJ->getInscritosActuales(),
                    'fecha_inicio' => date('Y-m-d\TH:i', strtotime($convocatoriaOBJ->getFechaInicio())),
                    'fecha_fin' => date('Y-m-d\TH:i', strtotime($convocatoriaOBJ->getFechaFin())),
                    'hora_limite_diaria' => substr($convocatoriaOBJ->getHoraLimiteDiaria(), 0, 5),
                    'activo' => $convocatoriaOBJ->getActivo(),
                    'created_at' => $convocatoriaOBJ->getCreatedAt(),
                ];
                $form->setData($data);
            }
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

        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE CONVOCATORIA NO VÁLIDO');
            return $this->redirect()->toRoute('formularios/convocatoria', ['action' => 'index']);
        }

        $convocatoria = $this->DAO->getConvocatoria($id);

        if (!$convocatoria) {
            $this->flashMessenger()->addErrorMessage('CONVOCATORIA NO ENCONTRADA');
            return $this->redirect()->toRoute('formularios/convocatoria', ['action' => 'index']);
        }

        // Obtener el nombre del formulario asociado
        $configOptions = $this->getConfiguracionesOptions();
        $nombreFormulario = $configOptions[$convocatoria->getIdConfig()] ?? 'No especificado';

        $view = new ViewModel([
            'convocatoria' => $convocatoria,
            'nombreFormulario' => $nombreFormulario
        ]);
        $view->setTerminal(true);
        return $view;
    }

    public function registrarAction()
    {
        $infoSesion = $this->getInfoSesion();
        $form = $this->getFormulario('registrar');
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }

        $postData = $request->getPost();

        // Eliminar campos vacíos que no deben validarse
        $filteredData = [];
        foreach ($postData as $key => $value) {
            if (($key == 'id_convocatoria' || $key == 'inscritos_actuales') && empty($value)) {
                continue;
            }
            $filteredData[$key] = $value;
        }

        $form->setData($filteredData);
        $convocatoriaOBJ = new Convocatoria();

        if (!$form->isValid()) {
            $messages = $form->getMessages();
            // Solo registrar error si hay problemas de validación
            $this->logError(new \Exception(json_encode($messages)), 'VALIDACIÓN CONVOCATORIA');
            $this->flashMessenger()->addErrorMessage('LA INFORMACIÓN DE REGISTRO NO ES VÁLIDA');
            return $this->redirect()->toRoute('formularios/convocatoria', ['action' => 'index']);
        }

        $data = $form->getData();

        // Convertir fechas
        if (!empty($data['fecha_inicio'])) {
            $data['fecha_inicio'] = str_replace('T', ' ', $data['fecha_inicio']) . ':00';
        }
        if (!empty($data['fecha_fin'])) {
            $data['fecha_fin'] = str_replace('T', ' ', $data['fecha_fin']) . ':00';
        }

        if (!empty($data['hora_limite_diaria']) && strlen($data['hora_limite_diaria']) == 5) {
            $data['hora_limite_diaria'] = $data['hora_limite_diaria'] . ':00';
        }

        $convocatoriaOBJ->exchangeArray($data);
        $convocatoriaOBJ->setCreatedAt(date('Y-m-d H:i:s'));
        $convocatoriaOBJ->setInscritosActuales(0);
        $convocatoriaOBJ->setActivo(1);

        try {
            $this->DAO->registrar($convocatoriaOBJ);
            $this->flashMessenger()->addSuccessMessage('LA CONVOCATORIA FUE REGISTRADA EXITOSAMENTE');
        } catch (\Exception $ex) {
            $this->logError($ex, 'REGISTRAR CONVOCATORIA');
            $this->flashMessenger()->addErrorMessage('ERROR AL REGISTRAR LA CONVOCATORIA');
        }

        return $this->redirect()->toRoute('formularios/convocatoria', ['action' => 'index']);
    }

    public function editarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);
            if ($id <= 0) {
                $this->flashMessenger()->addErrorMessage('ID DE CONVOCATORIA NO VÁLIDO');
                return $this->redirect()->toRoute('formularios/convocatoria', ['action' => 'index']);
            }

            // Obtener el formulario (igual que en registrar)
            $form = $this->getFormulario('editar', $id);

            // Verificar que el formulario tenga datos
            if (!$form->has('id_config')) {
                error_log("ERROR: El formulario no tiene campos");
            }

            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }

        // Procesar POST (igual que en registrar)
        $form = $this->getFormulario('editar');
        $convocatoriaOBJ = new Convocatoria();
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            $messages = $form->getMessages();
            $this->flashMessenger()->addErrorMessage('LA INFORMACIÓN A GUARDAR NO ES VÁLIDA');
            return $this->redirect()->toRoute('formularios/convocatoria', ['action' => 'index']);
        }

        $data = $form->getData();

        // Convertir fechas
        if (!empty($data['fecha_inicio'])) {
            $data['fecha_inicio'] = str_replace('T', ' ', $data['fecha_inicio']) . ':00';
        }
        if (!empty($data['fecha_fin'])) {
            $data['fecha_fin'] = str_replace('T', ' ', $data['fecha_fin']) . ':00';
        }

        if (!empty($data['hora_limite_diaria']) && strlen($data['hora_limite_diaria']) == 5) {
            $data['hora_limite_diaria'] = $data['hora_limite_diaria'] . ':00';
        }

        $convocatoriaOBJ->exchangeArray($data);
        $convocatoriaOBJ->setUpdatedAt(date('Y-m-d H:i:s'));

        try {
            $this->DAO->editar($convocatoriaOBJ);
            $this->flashMessenger()->addSuccessMessage('LA CONVOCATORIA FUE ACTUALIZADA EXITOSAMENTE');
        } catch (\Exception $ex) {
            $this->logError($ex, 'EDITAR CONVOCATORIA');
            $this->flashMessenger()->addErrorMessage('ERROR AL ACTUALIZAR LA CONVOCATORIA');
        }

        return $this->redirect()->toRoute('formularios/convocatoria', ['action' => 'index']);
    }

    public function eliminarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);

            if ($id <= 0) {
                $this->flashMessenger()->addErrorMessage('ID DE CONVOCATORIA NO VÁLIDO');
                return $this->redirect()->toRoute('formularios/convocatoria', ['action' => 'index']);
            }

            // Obtener la convocatoria
            $convocatoria = $this->DAO->getConvocatoria($id);

            if (!$convocatoria) {
                $this->flashMessenger()->addErrorMessage('CONVOCATORIA NO ENCONTRADA');
                return $this->redirect()->toRoute('formularios/convocatoria', ['action' => 'index']);
            }

            // Crear formulario simple para eliminar
            $form = new ConvocatoriaForm('eliminar', []);

            // Preparar datos
            $data = [
                'id_convocatoria' => $convocatoria->getIdConvocatoria(),
                'nombre_convocatoria' => $convocatoria->getNombreConvocatoria(),
                'periodo' => $convocatoria->getPeriodo(),
            ];
            $form->setData($data);

            $view = new ViewModel([
                'form' => $form,
                'convocatoria' => $convocatoria
            ]);
            $view->setTerminal(true);
            return $view;
        }

        // Procesar POST - eliminar
        $id = (int) $request->getPost('id_convocatoria', 0);

        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE CONVOCATORIA NO VÁLIDO');
            return $this->redirect()->toRoute('formularios/convocatoria', ['action' => 'index']);
        }

        // Verificar si tiene inscripciones asociadas
        $convocatoria = $this->DAO->getConvocatoria($id);
        if ($convocatoria && $convocatoria->getInscritosActuales() > 0) {
            $this->flashMessenger()->addErrorMessage('NO SE PUEDE ELIMINAR LA CONVOCATORIA PORQUE TIENE ' . $convocatoria->getInscritosActuales() . ' INSCRIPCIONES ASOCIADAS');
            return $this->redirect()->toRoute('formularios/convocatoria', ['action' => 'index']);
        }

        $convocatoriaOBJ = new Convocatoria();
        $convocatoriaOBJ->setIdConvocatoria($id);

        try {
            $this->DAO->eliminar($convocatoriaOBJ);
            $this->flashMessenger()->addSuccessMessage('LA CONVOCATORIA FUE ELIMINADA EXITOSAMENTE');
        } catch (\Exception $ex) {
            $this->logError($ex, 'ELIMINAR CONVOCATORIA');
            $this->flashMessenger()->addErrorMessage('ERROR AL ELIMINAR LA CONVOCATORIA: ' . $ex->getMessage());
        }

        return $this->redirect()->toRoute('formularios/convocatoria', ['action' => 'index']);
    }

    public function activarAction()
    {
        $id = (int) $this->params()->fromPost('id_convocatoria', $this->params()->fromQuery('id', 0));

        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE CONVOCATORIA NO VÁLIDO');
            return $this->redirect()->toRoute('formularios/convocatoria', ['action' => 'index']);
        }

        try {
            $this->DAO->cambiarEstado($id, 1);
            $this->flashMessenger()->addSuccessMessage('LA CONVOCATORIA FUE ACTIVADA');
        } catch (\Exception $ex) {
            $this->logError($ex, 'ACTIVAR CONVOCATORIA');
            $this->flashMessenger()->addErrorMessage('ERROR AL ACTIVAR LA CONVOCATORIA');
        }

        return $this->redirect()->toRoute('formularios/convocatoria', ['action' => 'index']);
    }

    public function desactivarAction()
    {
        $id = (int) $this->params()->fromPost('id_convocatoria', $this->params()->fromQuery('id', 0));

        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE CONVOCATORIA NO VÁLIDO');
            return $this->redirect()->toRoute('formularios/convocatoria', ['action' => 'index']);
        }

        try {
            $this->DAO->cambiarEstado($id, 0);
            $this->flashMessenger()->addSuccessMessage('LA CONVOCATORIA FUE DESACTIVADA');
        } catch (\Exception $ex) {
            $this->logError($ex, 'DESACTIVAR CONVOCATORIA');
            $this->flashMessenger()->addErrorMessage('ERROR AL DESACTIVAR LA CONVOCATORIA');
        }

        return $this->redirect()->toRoute('formularios/convocatoria', ['action' => 'index']);
    }

    private function logError($ex, $accion)
    {
        $msgLog = "\n" . date('Y-m-d H:i:s') . " $accion - ConvocatoriaController \n"
            . $ex->getMessage()
            . "\n" . $ex->getTraceAsString()
            . "\n----------------------------------------------------------------------- \n";
        $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
        fwrite($file, $msgLog);
        fclose($file);
    }

    private function writeLog($mensaje, $tipo = 'INFO')
    {
        // Solo registrar errores y excepciones
        if ($tipo == 'ERROR' || $tipo == 'EXCEPTION') {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " [$tipo] $mensaje \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
        }
    }
}
