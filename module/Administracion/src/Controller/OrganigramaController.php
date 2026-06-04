<?php

namespace Administracion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Administracion\Modelo\DAO\OrganigramaDAO;
use Administracion\Modelo\Entidades\Organigrama;
use Administracion\Formularios\OrganigramaForm;

class OrganigramaController extends AbstractActionController
{
    private $DAO;
    private $rutaLog = './public/log/';

    public function __construct(OrganigramaDAO $dao)
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
        $auth = new \Laminas\Authentication\AuthenticationService();
        if ($auth->hasIdentity()) {
            $infoSesion['login'] = $auth->getIdentity()->login;
            $infoSesion['idEmpleadoCliente'] = $auth->getIdentity()->idEmpleadoCliente;
        }
        return $infoSesion;
    }

    //------------------------------------------------------------------------------
    function getFormulario($action = '', $id = 0)
    {
        $nodosPadre = $this->DAO->getNodosParaPadre($id);
        $form = new OrganigramaForm($action, $nodosPadre);
        if ($id != 0) {
            $organigramaOBJ = $this->DAO->getNodo($id);
            $form->bind($organigramaOBJ);
        }
        return $form;
    }

    //------------------------------------------------------------------------------
    public function indexAction()
    {
        $filtro = "";
        return new ViewModel([
            'fetchAll' => $this->DAO->fetchAllConRuta(),  // Cambiar a fetchAllConRuta
        ]);
    }

    //------------------------------------------------------------------------------

    public function registrarAction()
    {
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];
        $nodosPadre = $this->DAO->getNodosParaPadre();

        $form = new OrganigramaForm('registrar', $nodosPadre);
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $view = new ViewModel([
                'form' => $form,
                'nodosPadre' => $nodosPadre,
            ]);
            $view->setTerminal(true);
            return $view;
        }

        $organigramaOBJ = new Organigrama();
        $form->setInputFilter($organigramaOBJ->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            // Mostrar errores de validación
            $messages = $form->getMessages();
            $errorText = '';
            foreach ($messages as $field => $errors) {
                $errorText .= "$field: " . implode(', ', $errors) . "; ";
            }
            $this->flashMessenger()->addErrorMessage('Error de validación: ' . $errorText);
            return $this->redirect()->toUrl('index');
        }

        $organigramaOBJ->exchangeArray($form->getData());
        $organigramaOBJ->setActivo(1);
        $organigramaOBJ->setRegistradopor($registradopor);
        $organigramaOBJ->setModificadopor('');
        $organigramaOBJ->setCreatedAt(date('Y-m-d H:i:s'));
        $organigramaOBJ->setUpdatedAt('0000-00-00 00:00:00');

        try {
            $id = $this->DAO->registrar($organigramaOBJ);
            $this->flashMessenger()->addSuccessMessage('EL NODO FUE REGISTRADO EN GESTORPORTAL');
        } catch (\Exception $ex) {
            $this->flashMessenger()->addErrorMessage('ERROR: ' . $ex->getMessage());
        }

        return $this->redirect()->toUrl('index');
    }
    //------------------------------------------------------------------------------
    public function editarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);
            if ($id == 0) {
                $id = (int) $this->params()->fromRoute('id1', 0);
            }

            $nodo = $this->DAO->getNodoDetalle($id);

            // Obtener todos los nodos disponibles (excluyendo el actual y sus hijos)
            $nodosPadre = $this->DAO->getNodosParaPadre($id);

            // Crear el formulario con los nodos padre
            $form = new OrganigramaForm('editar', $nodosPadre);

            if ($nodo) {
                $form->get('id')->setValue($nodo['id']);
                $form->get('nombre')->setValue($nodo['nombre']);
                $form->get('tipo')->setValue($nodo['tipo']);
                $form->get('padre_id')->setValue($nodo['padre_id']);  // Valor actual del padre
                /* $form->get('icono')->setValue($nodo['icono'] ?? ''); */
                $form->get('orden')->setValue($nodo['orden']);
                /* $form->get('color')->setValue($nodo['color'] ?? ''); */
                $form->get('descripcion')->setValue($nodo['metadata_descripcion'] ?? $nodo['descripcion'] ?? '');
                $form->get('estado')->setValue($nodo['activo'] == 1 ? 'Activo' : 'Inactivo');
                $form->get('registradopor')->setValue($nodo['registradopor'] ?? 'Sistema');
                $form->get('created_at')->setValue($nodo['created_at']);
            }

            // Depuración: verificar que los nodosPadre tienen datos
            error_log("EDITAR - NodosPadre: " . print_r($nodosPadre, true));

            $view = new ViewModel([
                'form' => $form,
                'nodo' => $nodo,
                'nodosPadre' => $nodosPadre,  // Pasar los nodosPadre a la vista
            ]);
            $view->setTerminal(true);
            return $view;
        }

        // PROCESAR POST - ACTUALIZAR
        $datos = $request->getPost()->toArray();

        error_log("EDITAR POST - Datos recibidos: " . print_r($datos, true));

        if (empty($datos['id'])) {
            $this->flashMessenger()->addErrorMessage('ID del nodo no encontrado');
            return $this->redirect()->toUrl('index');
        }

        $nodo = new Organigrama();
        $nodo->setId($datos['id']);
        $nodo->setNombre($datos['nombre']);
        $nodo->setTipo($datos['tipo'] ?? 'oficina');
        $nodo->setPadreId(!empty($datos['padre_id']) ? $datos['padre_id'] : null);
        $nodo->setIcono($datos['icono'] ?? '');
        $nodo->setDescripcion($datos['descripcion'] ?? '');
        $nodo->setOrden($datos['orden'] ?? 0);
        $nodo->setColor($datos['color'] ?? '');
        try {
            $this->DAO->editar($nodo);
            $this->flashMessenger()->addSuccessMessage('EL NODO FUE ACTUALIZADO CORRECTAMENTE');
        } catch (\Exception $ex) {
            $this->flashMessenger()->addErrorMessage('ERROR: ' . $ex->getMessage());
        }

        return $this->redirect()->toUrl('index');
    }
    //------------------------------------------------------------------------------
    public function detalleAction()
    {
        $id = (int) $this->params()->fromQuery('id', 0);
        if ($id == 0) {
            $id = (int) $this->params()->fromRoute('id1', 0);
        }

        // Obtener el nodo con todos sus datos
        $nodo = $this->DAO->getNodoDetalle($id);

        $view = new ViewModel([
            'nodo' => $nodo,  // Asegurar que se llama 'nodo'
        ]);
        $view->setTerminal(true);
        return $view;
    }

    //------------------------------------------------------------------------------
    public function eliminarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);
            if ($id == 0) {
                $id = (int) $this->params()->fromRoute('id1', 0);
            }

            $nodo = $this->DAO->getNodoDetalle($id);

            $view = new ViewModel([
                'nodo' => $nodo,
            ]);
            $view->setTerminal(true);
            return $view;
        }

        // Procesar POST - Eliminar
        $id = (int) $request->getPost('id', 0);

        if ($id == 0) {
            $this->flashMessenger()->addErrorMessage('ID del nodo no encontrado');
            return $this->redirect()->toUrl('index');
        }

        try {
            $this->DAO->eliminar($id);  // Pasar solo el ID, no un objeto
            $this->flashMessenger()->addSuccessMessage('EL NODO FUE ELIMINADO CORRECTAMENTE');
        } catch (\Exception $ex) {
            $this->flashMessenger()->addErrorMessage('ERROR: ' . $ex->getMessage());
        }

        return $this->redirect()->toUrl('index');
    }
    //------------------------------------------------------------------------------
    public function activarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('id', 0);
            if ($id == 0) {
                $id = (int) $this->params()->fromRoute('id1', 0);
            }

            $nodo = $this->DAO->getNodoDetalle($id);

            $view = new ViewModel([
                'nodo' => $nodo,
            ]);
            $view->setTerminal(true);
            return $view;
        }

        // Procesar POST - Activar
        $id = (int) $request->getPost('id', 0);

        if ($id == 0) {
            $this->flashMessenger()->addErrorMessage('ID del nodo no encontrado');
            return $this->redirect()->toUrl('index');
        }

        try {
            $this->DAO->activar($id);  // Pasar solo el ID
            $this->flashMessenger()->addSuccessMessage('EL NODO FUE ACTIVADO CORRECTAMENTE');
        } catch (\Exception $ex) {
            $this->flashMessenger()->addErrorMessage('ERROR: ' . $ex->getMessage());
        }

        return $this->redirect()->toUrl('index');
    }

    //------------------------------------------------------------------------------
    public function verAction()
    {
        $total = $this->DAO->verificarDatos();
        error_log("VER ACTION - Total nodos: " . $total);

        $arbol = $this->DAO->getTree();
        error_log("VER ACTION - Árbol: " . print_r($arbol, true));

        return new ViewModel([
            'arbol' => json_encode($arbol, JSON_UNESCAPED_UNICODE),
        ]);
    }

    //------------------------------------------------------------------------------
    public function apiArbolAction()
    {
        $arbol = $this->DAO->getTree();
        return new JsonModel($arbol);
    }

    //------------------------------------------------------------------------------
    public function apiHijosAction()
    {
        $id = (int) $this->params()->fromRoute('id1', 0);
        $hijos = $this->DAO->getHijos($id);
        return new JsonModel($hijos);
    }

    public function apiRutaAction()
    {
        $id = (int) $this->params()->fromQuery('id', 0);

        if ($id == 0) {
            return new JsonModel(['ruta' => '']);
        }

        $ruta = $this->DAO->getRutaCompletaPorId($id);

        return new JsonModel(['ruta' => $ruta]);
    }
}
