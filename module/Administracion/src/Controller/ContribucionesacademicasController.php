<?php

declare(strict_types=1);

namespace Administracion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Administracion\Modelo\DAO\ContribucionesacademicasDAO;
use Administracion\Formularios\ContribucionesacademicasForm;
use Administracion\Modelo\Entidades\Contribucionesacademicas;

class ContribucionesacademicasController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = './public/log/';
    private $rutaArchivos = '/var/www/html/newportal/archivos/contribuciones_academicas/';
    /* private $rutaArchivos = './../archivos/contribuciones_academicas/'; */
    //------------------------------------------------------------------------------

    public function __construct(ContribucionesacademicasDAO $dao)
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
    function getFormulario($action = '', $idCA = 0)
    {
        $form = new ContribucionesacademicasForm($action);
        if ($idCA != 0) {
            $lvmenOBJ = $this->DAO->getContribucionesacademicas($idCA);
            $form->bind($lvmenOBJ);
        }
        return $form;
    }
    function getFormularioPrograma($idPrograma = 0)
    {
        $facultades = $this->DAO->getFacultades();
        $facultadSelect = array();
        foreach ($facultades as $fac) {
            $facultadSelect[$fac['idFacultad']] = $fac['facultad'];
        }
        if ($idPrograma != 0) {
            $programaOBJ = $this->DAO->getProgramasOBJ($idPrograma);
            $tipos = $this->DAO->getTipoProceso($programaOBJ->getIdProceso());
            $subProcesos = $this->DAO->getSubproceso($programaOBJ->getIdTipoProceso());
            $tipoSelect = array();
            $subProcesoSelect = array();
            foreach ($tipos as $tipo) {
                $tipoSelect[$tipo['idTipoProceso']] = $tipo['tipoProceso'];
            }
            foreach ($subProcesos as $subProceso) {
                $subProcesoSelect[$subProceso['idSubproceso']] = $subProceso['subproceso'];
            }
            $form = new ProcesoForm($procesoSelect, $tipoSelect, $subProcesoSelect);
            $form->bind($programaOBJ);
        } else {
            $form = new ProcesoForm($procesoSelect);
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
        $registradopor = $infosesion['login'];
        $facultades = $this->DAO->getFacultades();
        $listaFacultades = array();
        foreach ($facultades  as $dep) {
            $listaFacultades[$dep['idFacultad']] = $dep['facultad'];
        }

        $form = new ContribucionesacademicasForm('registrar', $listaFacultades);
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }

        $lvmenOBJ = new Contribucionesacademicas();
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            print_r($form->getMessages());
            return ['form' => $form];
            exit();
        }

        $files = $request->getFiles()->toArray();
        $uploadOK = new \Laminas\Validator\File\UploadFile();

        // Validación del archivo obligatorio 'archivo'
        if (!$uploadOK->isValid($files['archivo'])) {
            $this->flashMessenger()->addErrorMessage('EL ARCHIVO ADJUNTO ES OBLIGATORIO Y PRESENTA ERRORES AL CARGAR.');
            return $this->redirect()->toUrl('index');
        }

        // Validar y procesar el archivo 'archivo'
        if (array_key_exists('archivo', $files)) {
            $ext = pathinfo($files['archivo']['name'], PATHINFO_EXTENSION);
            $filesize = new \Laminas\Validator\File\Size(['min' => '250B', 'max' => '25MB']);
            if (!$filesize->isValid($files['archivo'])) {
                $this->flashMessenger()->addErrorMessage('EL ARCHIVO ADJUNTO NO ESTÁ EN LOS LÍMITES PERMITIDOS.');
                return $this->redirect()->toUrl('index');
            }
            $extensiones = new \Laminas\Validator\File\Extension(['extension' => ['pdf']]);
            if (!$extensiones->isValid($files['archivo'])) {
                $this->flashMessenger()->addErrorMessage('EL ARCHIVO ADJUNTO NO ES PERMITIDO.');
                return $this->redirect()->toUrl('index');
            }
            $filter = new \Laminas\Filter\File\RenameUpload(['target' => $this->rutaArchivos . 'archivos/' . 'CA' . '.' . $ext, 'randomize' => true]);
            $upload = $filter->filter($files['archivo']);
            if ($upload['error'] != 0) {
                $this->flashMessenger()->addErrorMessage('NO FUE POSIBLE SUBIR EL ARCHIVO ADJUNTO.');
                return $this->redirect()->toUrl('index');
            }
            $respaldoArchivo = basename($upload['tmp_name']);
        }

        // Validar y procesar el archivo opcional 'imagen'
        $respaldoImagen = 'default.png'; // Imagen por defecto
        if (array_key_exists('imagen', $files) && $files['imagen']['error'] == UPLOAD_ERR_OK) {
            $ext = pathinfo($files['imagen']['name'], PATHINFO_EXTENSION);
            $filesize = new \Laminas\Validator\File\Size(['min' => '250B', 'max' => '2MB']);
            if (!$filesize->isValid($files['imagen'])) {
                $this->flashMessenger()->addErrorMessage('EL ARCHIVO IMAGEN NO ESTÁ EN LOS LÍMITES PERMITIDOS.');
                return $this->redirect()->toUrl('index');
            }
            $extensiones = new \Laminas\Validator\File\Extension(['extension' => ['jpg', 'jpeg', 'png', 'gif']]);
            if (!$extensiones->isValid($files['imagen'])) {
                $this->flashMessenger()->addErrorMessage('EL ARCHIVO IMAGEN NO ES PERMITIDO.');
                return $this->redirect()->toUrl('index');
            }
            $filter = new \Laminas\Filter\File\RenameUpload(['target' => $this->rutaArchivos . 'imagenes/' . 'CA' . '.' . $ext, 'randomize' => true]);
            $upload = $filter->filter($files['imagen']);
            if ($upload['error'] != 0) {
                $this->flashMessenger()->addErrorMessage('NO FUE POSIBLE SUBIR EL ARCHIVO IMAGEN.');
                return $this->redirect()->toUrl('index');
            }
            $respaldoImagen = basename($upload['tmp_name']);
        }

        // Guardar los datos
        $lvmenOBJ->exchangeArray($form->getData());
        $lvmenOBJ->setImagen($respaldoImagen); // Imagen (por defecto o cargada)
        $lvmenOBJ->setArchivo($respaldoArchivo); // Archivo obligatorio
        $lvmenOBJ->setEstado('Activo');
        $lvmenOBJ->setRegistradopor($registradopor);
        $lvmenOBJ->setModificadopor('');
        $lvmenOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $lvmenOBJ->setFechahoramod('0000-00-00 00:00:00');

        try {
            $this->DAO->registrar($lvmenOBJ);
            $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE REGISTRADO EN JIMSOFT');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR ARCHIVO - ArchivoController->registrar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EL ARCHIVO NO FUE REGISTRADO.');
        }

        return $this->redirect()->toUrl('index');
    }


    //------------------------------------------------------------------------------  
    public function editarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idCA = (int) $this->params()->fromQuery('idCA', 0);
            $infoContribucionesacademicas = $this->DAO->getContribucionesacademicasDetalle($idCA);

            // Obtener facultades
            $facultades = $this->DAO->getFacultades();
            $listaFacultades = array();
            foreach ($facultades as $dep) {
                $listaFacultades[$dep['idFacultad']] = $dep['facultad'];
            }

            // Obtener programas de la facultad seleccionada
            $idFacultadSeleccionada = $infoContribucionesacademicas['idF'];
            $programas = [];
            $listaProgramas = [];

            if ($idFacultadSeleccionada) {
                $programas = $this->DAO->getProgramasPorFacultad($idFacultadSeleccionada);
                foreach ($programas as $prog) {
                    $listaProgramas[$prog['idPrograma']] = $prog['programa'];
                }
            }

            // Crear formulario con ambos select
            $form = new ContribucionesacademicasForm('editar', $listaFacultades, $listaProgramas);

            // Establecer los datos en el formulario
            $form->setData($infoContribucionesacademicas);

            // Configurar el valor seleccionado en el select de facultades
            $form->get('idFacultad')->setValue($idFacultadSeleccionada);

            // Configurar el valor seleccionado en el select de programas
            if (isset($infoContribucionesacademicas['idPrograma'])) {
                $form->get('idPrograma')->setValue($infoContribucionesacademicas['idPrograma']);
            }

            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }

        // Resto del código para manejar el POST...
        $form = new ContribucionesacademicasForm('editar');
        $clOBJ = new Contribucionesacademicas();
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            $this->flashMessenger()->addErrorMessage('LA INFORMACION A GUARDAR NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }

        try {
            $clOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $clOBJ->setModificadopor($modificadopor);
            $clOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->editar($clOBJ);
            $this->flashMessenger()->addSuccessMessage('LA INFORMACION DEL ARCHIVO FUE ACTUALIZADA');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ACTUALIZAR CONTRATO LABORAL - ArchivoController->editar \n"
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
        $idCA = (int) $this->params()->fromQuery('idCA', 0);
        $infoEmpleado = $this->DAO->getContribucionesacademicasDetalle($idCA);
        $view = new ViewModel(['form' => $infoEmpleado]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------
    public function eliminarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idCA = (int) $this->params()->fromQuery('idCA', 0);
            $infoCA = $this->DAO->getContribucionesacademicasDetalle($idCA);
            // Obtener facultades
            $facultades = $this->DAO->getFacultades();
            $listaFacultades = array();
            foreach ($facultades as $dep) {
                $listaFacultades[$dep['idFacultad']] = $dep['facultad'];
            }

            // Obtener programas de la facultad seleccionada
            $idFacultadSeleccionada = $infoCA['idF'];
            $programas = [];
            $listaProgramas = [];

            if ($idFacultadSeleccionada) {
                $programas = $this->DAO->getProgramasPorFacultad($idFacultadSeleccionada);
                foreach ($programas as $prog) {
                    $listaProgramas[$prog['idPrograma']] = $prog['programa'];
                }
            }
            $form = new ContribucionesacademicasForm('eliminar', $listaFacultades, $listaProgramas);
            $form->setData($infoCA);
            // Configurar el valor seleccionado en el select de facultades
            $form->get('idFacultad')->setValue($idFacultadSeleccionada);
            // Configurar el valor seleccionado en el select de programas
            if (isset($infoCA['idPrograma'])) {
                $form->get('idPrograma')->setValue($infoCA['idPrograma']);
            }
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new ContribucionesacademicasForm('eliminar');
        $lvmenOBJ = new Contribucionesacademicas();
        $form->setInputFilter($lvmenOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            /* print_r($form->getMessages());
            return ['form' => $form]; */
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE ELIMINACION DEL ARCHIVO NO ES VALIDA');
            return $this->redirect()->toUrl('index?idCA=' . $this->params()->fromPost('idCA', 0));
        }
        //----------------------------------------------------------------------
        try {
            $lvmenOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $lvmenOBJ->setModificadopor($modificadopor);
            $lvmenOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->eliminar($lvmenOBJ);
            $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE ELIMINADA DE JIMSOFT');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ELIMINAR ARCHIVO - ContratolaboralController->eliminar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL ARCHIVO NO FUE ELIMINADA DE JIMSOFT.');
        }
        return $this->redirect()->toUrl('index?idCA=' . $lvmenOBJ->getidCA());
    }
    public function activarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idCA = (int) $this->params()->fromQuery('idCA', 0);
            $infoCA = $this->DAO->getContribucionesacademicasDetalle($idCA);
            // Obtener facultades
            $facultades = $this->DAO->getFacultades();
            $listaFacultades = array();
            foreach ($facultades as $dep) {
                $listaFacultades[$dep['idFacultad']] = $dep['facultad'];
            }

            // Obtener programas de la facultad seleccionada
            $idFacultadSeleccionada = $infoCA['idF'];
            $programas = [];
            $listaProgramas = [];

            if ($idFacultadSeleccionada) {
                $programas = $this->DAO->getProgramasPorFacultad($idFacultadSeleccionada);
                foreach ($programas as $prog) {
                    $listaProgramas[$prog['idPrograma']] = $prog['programa'];
                }
            }
            $form = new ContribucionesacademicasForm('activar', $listaFacultades, $listaProgramas);
            $form->setData($infoCA);
            // Configurar el valor seleccionado en el select de facultades
            $form->get('idFacultad')->setValue($idFacultadSeleccionada);
            // Configurar el valor seleccionado en el select de programas
            if (isset($infoCA['idPrograma'])) {
                $form->get('idPrograma')->setValue($infoCA['idPrograma']);
            }
            $view = new ViewModel([
                'form' => $form
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new ContribucionesacademicasForm('activar');
        $lvmenOBJ = new Contribucionesacademicas();
        $form->setInputFilter($lvmenOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            //print_r($form->getMessages());
            //return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE ELIMINACION DEL ARCHIVO NO ES VALIDA');
            return $this->redirect()->toUrl('index?idCA=' . $this->params()->fromPost('idCA', 0));
        }
        //----------------------------------------------------------------------
        try {
            $lvmenOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $lvmenOBJ->setModificadopor($modificadopor);
            $lvmenOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->activar($lvmenOBJ);
            $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE RECUPERADO DE GESTORPORTAL');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ELIMINAR ARCHIVO - ArchivoController->eliminar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL ARCHIVO NO FUE ELIMINADA DE GESTORPORTAL.');
        }
        return $this->redirect()->toUrl('index?idCA=' . $lvmenOBJ->getidCA());
    }
    //------------------------------------------------------------------------------  
    public function actualizararchivoAction()
    {
        $idCA = (int) $this->params()->fromQuery('idCA', 0);
        $form = $this->getFormulario('actualizararchivo', $idCA);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $session = $this->getInfoSesion();
                $lvmenOBJ = new Contribucionesacademicas($form->getData());
                //----------------------------------------------------------------------
                $files = $request->getFiles()->toArray();
                //----------------------------------------------------------------------
                $uploadOK = new \Laminas\Validator\File\UploadFile();
                if (!$uploadOK->isValid($files['imagen'])) {
                    $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO PRESENTA ERRORES AL CARGAR AL SERVIDOR');
                    return $this->redirect()->toUrl('index');
                }
                if (array_key_exists('imagen', $files)) {
                    $ext = pathinfo($files['imagen']['name'], PATHINFO_EXTENSION);
                    $filesize = new \Laminas\Validator\File\Size([
                        'min' => '250B',
                        'max' => '25MB',
                    ]);
                    if (!$filesize->isValid($files['imagen'])) {
                        $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO NO ESTA EN LOS LIMITES PERMITIDOS. <br> MINIMO: 250B  <br> MAXIMO: <b>2MB</b>');
                        return $this->redirect()->toUrl('index');
                    }
                    $extensiones = new \Laminas\Validator\File\Extension(array('extension' => array('jpg', 'jpeg', 'png', 'gif')));
                    if (!$extensiones->isValid($files['imagen'])) {
                        $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO NO ES PERMITIDO. <br> ARCHIVOS PERMITIDOS: <br> txt');
                        return $this->redirect()->toUrl('index');
                    }
                    $filter = new \Laminas\Filter\File\RenameUpload([
                        'target' => $this->rutaArchivos . 'CPR' . '.' . $ext,
                        'randomize' => true,
                    ]);
                    //----------------------------------------------------------------------
                    $upload = $filter->filter($files['imagen']);
                    //----------------------------------------------------------------------
                    if ($upload['error'] != 0) {
                        $this->flashMessenger()->addErrorMessage('NO FUE POSIBLE SUBIR EL ARCHIVO DE RESPALDO ADJUNTO.');
                        return $this->redirect()->toUrl('index');
                    }
                    $respaldo = basename($upload['tmp_name']);
                    //----------------------------------------------------------------------
                }
                $lvmenOBJ->setImagen($respaldo);
                $lvmenOBJ->setModificadopor($session['login']);
                $lvmenOBJ->setFechahoramod(date('Y-m-d H:i:s'));
                try {
                    $this->DAO->editar($lvmenOBJ);
                    $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE EDITADO EN GESTORPORTAL');
                    return $this->redirect()->toUrl('index');
                } catch (\Exception $ex) {
                    $msgLog = "\n" . date('Y-m-d H:i:s') . " EDITAR Archivo - ArchivoController->registrar \n"
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
    public function getSelectProgramaAction()
    {
        $idFacultad = (int) $this->params()->fromQuery('idFacultad', 0);
        $view = new ViewModel(array(
            'programas' => $this->DAO->getProgramas($idFacultad),
        ));
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------
}
