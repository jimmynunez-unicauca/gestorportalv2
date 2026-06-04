<?php

declare(strict_types=1);

namespace Usermanager\Controller;

use Laminas\Crypt\Password\Bcrypt;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Usermanager\Modelo\DAO\AccesoDAO;
use Usermanager\Formularios\AccesoForm;
use Usermanager\Modelo\Entidades\Acceso;

class AccesoController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = './public/log/';
    //------------------------------------------------------------------------------

    public function __construct(AccesoDAO $dao)
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
        $filtro = "empleadocliente.idEmpleadoCliente NOT IN (SELECT empleadocliente.idEmpleadoCliente FROM empleadocliente INNER JOIN usuario ON empleadocliente.idEmpleadoCliente = usuario.idEmpleadoCliente WHERE usuario.idUsuario > 0) and empleadocliente.estado = 'Activo'";
        $empleados = $this->DAO->getEmpleados($filtro);
        $listaEmpleado = array();
        foreach ($empleados  as $empleado) {
            $listaEmpleado[$empleado['idEmpleadoCliente']] = $empleado['nombre'] . ' ' . $empleado['apellido'];
        }
        $roles = $this->DAO->getRoles();
        $listaRoles = array();
        foreach ($roles  as $rol) {
            $listaRoles[$rol['idRol']] = $rol['rol'];
        }
        //----------------------------------------------------------------------
        $form = new AccesoForm('registrar', $listaEmpleado, $listaRoles);
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $accesoOBJ = new Acceso();
        $form->setInputFilter($accesoOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            /* print_r($form->getMessages());
            return ['form' => $form]; */
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL USUARIO NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        $accesoOBJ->exchangeArray($form->getData());
        $user = $this->DAO->getEmpleadoCliente($accesoOBJ->getIdEmpleadoCliente());
        $accesoOBJ->setPassword((new Bcrypt())->create($accesoOBJ->getPassword()));
        if ($user['genero'] == 'Masculino') {
            $accesoOBJ->setFoto('perfilHombre.png');
        } else {
            $accesoOBJ->setFoto('perfilMujer.png');
        }
        $accesoOBJ->setEstado('Activo');
        $accesoOBJ->setRegistradopor($registradopor);
        $accesoOBJ->setModificadopor('');
        $accesoOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $accesoOBJ->setFechahoramod('0000-00-00 00:00:00');
        try {
            $idRol = (int) $this->params()->fromPost('idRol', 0);
            $this->DAO->registrar($accesoOBJ, $idRol);
            $this->flashMessenger()->addSuccessMessage('EL USUARIO FUE REGISTRADO EN JIMSOFT');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR USUARIO - ContratolaboralController->registrar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EL USUARIO NO FUE REGISTRADO EN JIMSOFT.');
        }

        return $this->redirect()->toUrl('index');
    }

    public function getLoginAction()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();
        $error = 1;
        $login = '';
        if ($request->isGet()) {
            $cont = 0;
            $idEmpleadoCliente = $this->params()->fromQuery('idEmpleadoCliente', 0);
            $empleadoOBJ =  $this->DAO->getEmpleadoCliente($idEmpleadoCliente);
            $nombres = $empleadoOBJ['nombre'];
            $apellidos = $empleadoOBJ['apellido'];
            $usuario = trim($nombres) . ' ' . trim($apellidos);
            $partesApellidos = explode(' ', $apellidos);
            $primerApellido = $partesApellidos[0];
            $login = strtolower($nombres[0] . $primerApellido);
            while ($this->DAO->existeLogin($login) && $cont < 100) {
                $login = $login . rand(1, 1000);
                $cont++;
            }
            if ($cont <= 100) {
                $error = 0;
            }
        }
        return new JsonModel(array(
            'error' => $error,
            'login' => $login,
            'usuario' => $usuario,
        ));
        return $response;
    }

    //------------------------------------------------------------------------------  
    public function editarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idUsuario = (int) $this->params()->fromQuery('idUsuario', 0);
            $infoUser = $this->DAO->getUsuarioDetalle($idUsuario);
            $roles = $this->DAO->getRoles();
            $listaRoles = array();
            foreach ($roles  as $rol) {
                $listaRoles[$rol['idRol']] = $rol['rol'];
            }
            $form = new AccesoForm('editar',  array(), $listaRoles);
            $form->setData($infoUser);
            $view = new ViewModel([
                'form' => $form,
                'idUsuario' => $idUsuario,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new AccesoForm('editar');
        $accesoOBJ = new Acceso();
        $form->setInputFilter($accesoOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            /*  print_r($form->getMessages());
            return ['form' => $form]; */
            $this->flashMessenger()->addErrorMessage('LA INFORMACION A GUARDAR NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        //----------------------------------------------------------------------
        try {
            $accesoOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $accesoOBJ->setModificadopor($modificadopor);
            $accesoOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $idRol = (int) $this->params()->fromPost('idRol', 0);
            $idUser = (int) $this->params()->fromPost('idUser', 0);
            $this->DAO->editar($idUser, $idRol);
            $this->flashMessenger()->addSuccessMessage('LA INFORMACION DEL USUARIO FUE ACTUALIZADA EN JIMSOFT');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ACTUALIZAR USUARIO - ContratolaboralController->editar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'JimSoft.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL USUARIO NO FUE ACTUALIZADO EN JIMSOFT.');
        }
        return $this->redirect()->toUrl('index');
    }

    //------------------------------------------------------------------------------  
    public function detalleAction()
    {
        $idUsuario = (int) $this->params()->fromQuery('idUsuario', 0);
        $infoCL = $this->DAO->getUsuarioDetalle($idUsuario);
        $view = new ViewModel(['form' => $infoCL]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------

    public function eliminarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idUsuario = (int) $this->params()->fromQuery('idUsuario', 0);
            $estado = $this->params()->fromQuery('estado', '');
            $infoUser = $this->DAO->getUsuarioDetalle($idUsuario);
            $roles = $this->DAO->getRoles();
            $listaRoles = array();
            foreach ($roles  as $rol) {
                $listaRoles[$rol['idRol']] = $rol['rol'];
            }
            $form = new AccesoForm('eliminar',  array(), $listaRoles);
            $form->setData($infoUser);
            $view = new ViewModel([
                'form' => $form,
                'idUsuario' => $idUsuario,
                'estadoAux' => $estado,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new AccesoForm('eliminar');
        $accesoOBJ = new Acceso();
        $form->setInputFilter($accesoOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            /*  print_r($form->getMessages());
            return ['form' => $form]; */
            $this->flashMessenger()->addErrorMessage('LA INFORMACION A GUARDAR NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        //----------------------------------------------------------------------
        try {
            $accesoOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $accesoOBJ->setModificadopor($modificadopor);
            $accesoOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $idUser = (int) $this->params()->fromPost('idUser', 0);
            $estadoAux =  $this->params()->fromPost('estadoAux', '');
            $this->DAO->eliminar($idUser, $estadoAux);
            $this->flashMessenger()->addSuccessMessage('LA INFORMACION DEL USUARIO FUE ACTUALIZADA EN JIMSOFT');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ACTUALIZAR USUARIO - ContratolaboralController->editar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'JimSoft.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL USUARIO NO FUE ACTUALIZADO EN JIMSOFT.');
        }
        return $this->redirect()->toUrl('index');
    }

    //------------------------------------------------------------------------------ 

    public function buscarEmpleadoAction()
    {
        $autocompletar = trim($this->params()->fromQuery('buscarEmpleado', ''));
        $infoEmpleado = array();
        if ($autocompletar != '') {
            $infoEmpleado = $this->DAO->getInfoEmpleado($autocompletar);
        }
        return new JsonModel($infoEmpleado);
    }
    //------------------------------------------------------------------------------   
    public function getEmpleadoAction()
    {
        $identificacion = (int) $this->params()->fromQuery('identificacion', 0);
        $idEmpleadoCliente = (int) $this->params()->fromQuery('idEmpleadoCliente', 0);
        $infoEmpleado = null;
        if ($identificacion > 0) {
            $infoEmpleado = $this->DAO->getEmpleadoByIdentificacion($identificacion);
        } else {
            if ($idEmpleadoCliente > 0) {
                $infoEmpleado = $this->DAO->getEmpleado($idEmpleadoCliente);
            }
        }
        $view = new ViewModel([
            'infoEmpleado' => $infoEmpleado,
        ]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------   
}
