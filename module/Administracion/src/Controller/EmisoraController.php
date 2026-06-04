<?php

declare(strict_types=1);

namespace Administracion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Administracion\Modelo\DAO\EmisoraDAO;
use Administracion\Formularios\ProgramaForm;
use Administracion\Formularios\PodcastForm;
use Administracion\Modelo\Entidades\Programa;
use Administracion\Modelo\Entidades\Podcast;

class EmisoraController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = './public/log/';
    private $rutaArchivos = '/var/www/html/newportal/archivos/emisora/';
    /* private $rutaArchivos = './../archivos/emisora/'; */
    private string $ftpHost = 'ftp.emisora.unicauca.edu.co';
    private string $ftpUser = 'usuario_ftp';
    private string $ftpPass = 'password_ftp';
    private string $ftpBasePath = '/podcasts/';
    private bool $ftpPassive = true;

    //------------------------------------------------------------------------------

    public function __construct(EmisoraDAO $dao)
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
    function getFormulario($action = '', $idPrograma = 0)
    {
        $form = new ProgramaForm($action);
        if ($idPrograma != 0) {
            $emisoraOBJ = $this->DAO->getPrograma($idPrograma);
            $form->bind($emisoraOBJ);
        }
        return $form;
    }
    function getFormularioPodcast($action = '', $idPodcast = 0)
    {
        $form = new PodcastForm($action);
        if ($idPodcast != 0) {
            $emisoraOBJ = $this->DAO->getPodcastDetalle($idPodcast);
            $form->bind($emisoraOBJ);
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
    public function indexPodcastAction()
    {
        $filtro = "";
        return new ViewModel([
            'fetchAll' => $this->DAO->podcastAll($filtro),
        ]);
    }

    //------------------------------------------------------------------------------
    public function registrarAction()
    {
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];
        //----------------------------------------------------------------------
        $form = new ProgramaForm('registrar');
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $emisoraOBJ = new Programa();
        $form->setInputFilter($emisoraOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            /* print_r($form->getMessages());
            return ['form' => $form]; */
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL PROGRAMA NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        //----------------------------------------------------------------------
        $files = $request->getFiles()->toArray();
        $respaldoImagen = $this->procesarArchivo($files, 'imagen', ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'], '250B', '500KB', 'programas/PROGRAMA', 'sinImagen.png');
        if ($respaldoImagen === false) return $this->redirect()->toUrl('index');
        //----------------------------------------------------------------------
        $emisoraOBJ->exchangeArray($form->getData());
        $dias = $this->params()->fromPost('dias', []);
        $emisoraOBJ->setImagen($respaldoImagen);
        $emisoraOBJ->setEstado('Activo');
        $emisoraOBJ->setRegistradopor($registradopor);
        $emisoraOBJ->setModificadopor('');
        $emisoraOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $emisoraOBJ->setFechahoramod('0000-00-00 00:00:00');
        try {
            $this->DAO->registrar($emisoraOBJ, $dias);
            $this->flashMessenger()->addSuccessMessage('EL PROGRAMA FUE REGISTRADO EN GESTORPORTAL');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR PROGRAMA - EmisoraController->registrar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EL PROGRAMA NO FUE REGISTRADO EN GESTORPORTAL.');
        }
        return $this->redirect()->toUrl('index');
    }
    public function registrarPodcastAction()
    {
        $infosesion = $this->getInfoSesion();
        $registradopor = $infosesion['login'];
        $form = new PodcastForm('registrar-podcast');
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $emisoraOBJ = new Podcast();
        $form->setInputFilter($emisoraOBJ->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL PODCAST NO ES VALIDA');
            return $this->redirect()->toUrl('index-podcast');
        }

        $files = $request->getFiles()->toArray();
        $respaldoImagen = $this->procesarArchivo($files, 'imagen', ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'], '250B', '500KB', 'podcast/PODCAST', 'sinImagen.png');
        if ($respaldoImagen === false) return $this->redirect()->toUrl('index-podcast');

        $respaldoAudio = $this->procesarArchivo($files, 'audio_url', ['mp3', 'wav', 'aac'], '1KB', '25MB', 'audios/PODCAST', 'sinAudio.mp3');
        if ($respaldoAudio === false) return $this->redirect()->toUrl('index-podcast');

        $emisoraOBJ->exchangeArray($form->getData());
        $emisoraOBJ->setImagen($respaldoImagen);
        $emisoraOBJ->setAudio_url($respaldoAudio);
        $emisoraOBJ->setEstado('Activo');
        $emisoraOBJ->setRegistradopor($registradopor);
        $emisoraOBJ->setModificadopor('');
        $emisoraOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $emisoraOBJ->setFechahoramod('0000-00-00 00:00:00');

        try {
            $this->DAO->registrarPodcast($emisoraOBJ);
            $this->flashMessenger()->addSuccessMessage('EL PODCAST FUE REGISTRADO EN GESTORPORTAL');
        } catch (\Exception $ex) {
            $this->registrarLogError('REGISTRAR PODCAST', $ex);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EL PODCAST NO FUE REGISTRADO EN GESTORPORTAL.');
        }

        return $this->redirect()->toUrl('index-podcast');
    }



    //------------------------------------------------------------------------------  
    public function editarAction()
    {
        $idPrograma = (int) $this->params()->fromQuery('idPrograma', 0);
        $form = $this->getFormulario('editar', $idPrograma);
        $diasSeleccionados = $this->DAO->getDias($idPrograma);
        $form->get('dias')->setValue($diasSeleccionados);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $session = $this->getInfoSesion();
                $emisoraOBJ = new Programa($form->getData());
                $dias = $this->params()->fromPost('dias', []);
                $emisoraOBJ->setModificadopor($session['login']);
                $emisoraOBJ->setFechahoramod(date('Y-m-d H:i:s'));
                try {
                    //$this->DAO->eliminarDias($emisoraOBJ->getIdPrograma());
                    $this->DAO->editar($emisoraOBJ, $dias);
                    $this->flashMessenger()->addSuccessMessage('EL PROGRAMA FUE EDITADO EN GESTORPORTAL');
                    return $this->redirect()->toUrl('index');
                } catch (\Exception $ex) {
                    $msgLog = "\n" . date('Y-m-d H:i:s') . " EDITAR PROGRAMA - EmisoraController->editar \n"
                        . $ex->getMessage()
                        . "\n----------------------------------------------------------------------- \n";
                    $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
                    fwrite($file, $msgLog);
                    fclose($file);
                    $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL PROGRAMA NO FUE EDITADO EN GESTORPORTAL.');
                }
            } else {
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE, EL PROGRAMA NO FUE EDITADO EN GESTORPORTAL');
                return $this->redirect()->toUrl('index');
            }
        }
        $view = new ViewModel(['form' => $form]);
        $view->setTerminal(true);
        return $view;
    }
    public function editarPodcastAction()
    {
        $idPodcast = (int) $this->params()->fromQuery('idPodcast', 0);
        $form = $this->getFormularioPodcast('editar-podcast', $idPodcast);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $session = $this->getInfoSesion();
                $emisoraOBJ = new Podcast($form->getData());
                $emisoraOBJ->setModificadopor($session['login']);
                $emisoraOBJ->setFechahoramod(date('Y-m-d H:i:s'));
                try {
                    $this->DAO->editarPodcast($emisoraOBJ);
                    $this->flashMessenger()->addSuccessMessage('EL PODCAST FUE EDITADO EN GESTORPORTAL');
                    return $this->redirect()->toUrl('index-podcast');
                } catch (\Exception $ex) {
                    $msgLog = "\n" . date('Y-m-d H:i:s') . " EDITAR PODCAST - EmisoraController->editarPodcast \n"
                        . $ex->getMessage()
                        . "\n----------------------------------------------------------------------- \n";
                    $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
                    fwrite($file, $msgLog);
                    fclose($file);
                    $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL PROGRAMA NO FUE EDITADO EN GESTORPORTAL.');
                }
            } else {
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE, EL PROGRAMA NO FUE EDITADO EN GESTORPORTAL');
                return $this->redirect()->toUrl('index-podcast');
            }
        }
        $view = new ViewModel(['form' => $form]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------  
    public function detalleAction()
    {
        $idPrograma = (int) $this->params()->fromQuery('idPrograma', 0);
        $infoEmisora = $this->DAO->getProgramaDetalle($idPrograma);
        $dias = $this->DAO->getDias($idPrograma);
        $view = new ViewModel(['form' => $infoEmisora, 'dias' => $dias]);
        $view->setTerminal(true);
        return $view;
    }
    public function detallePodcastAction()
    {
        $idPodcast = (int) $this->params()->fromQuery('idPodcast', 0);
        $infoEmisora = $this->DAO->getPodcastDetalle($idPodcast);
        $view = new ViewModel(['form' => $infoEmisora]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------
    public function eliminarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idPrograma = (int) $this->params()->fromQuery('idPrograma', 0);
            $diasSeleccionados = $this->DAO->getDias($idPrograma);
            $infoLven = $this->DAO->getProgramaDetalle($idPrograma);
            $form = new ProgramaForm('eliminar');
            $form->get('dias')->setValue($diasSeleccionados);
            $form->setData($infoLven);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new ProgramaForm('eliminar');
        $programaOBJ = new Programa();
        $form->setInputFilter($programaOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            //print_r($form->getMessages());
            //return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE ELIMINACION DEL PROGRAMA NO ES VALIDA');
            return $this->redirect()->toUrl('index?idPrograma=' . $this->params()->fromPost('idPrograma', 0));
        }
        //----------------------------------------------------------------------
        try {
            $programaOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $programaOBJ->setModificadopor($modificadopor);
            $programaOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->eliminarActivar($programaOBJ, 'Eliminado');
            $this->flashMessenger()->addSuccessMessage('EL PROGRAMA FUE ELIMINADA DE GESTORPORTAL');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ELIMINAR PROGRAMA - EmisoraController->eliminar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL PROGRAMA NO FUE ELIMINADA DE GESTORPORTAL.');
        }
        return $this->redirect()->toUrl('index?idPrograma=' . $programaOBJ->getIdPrograma());
    }
    public function eliminarPodcastAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idPodcast = (int) $this->params()->fromQuery('idPodcast', 0);
            $infoLven = $this->DAO->getPodcastDetalle($idPodcast);
            $form = new PodcastForm('eliminar-podcast');
            $form->bind($infoLven);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new PodcastForm('eliminar-podcast');
        $programaOBJ = new Podcast();
        $form->setInputFilter($programaOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            //print_r($form->getMessages());
            //return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE ELIMINACION DEL ARCHIVO NO ES VALIDA');
            return $this->redirect()->toUrl('index-podcast?idPrograma=' . $this->params()->fromPost('idPrograma', 0));
        }
        //----------------------------------------------------------------------
        try {
            $programaOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $programaOBJ->setModificadopor($modificadopor);
            $programaOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->eliminarActivarPodcast($programaOBJ, 'Eliminado');
            $this->flashMessenger()->addSuccessMessage('EL PODCAST FUE ELIMINADA DE GESTORPORTAL');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ELIMINAR PODCAST - EmisoraController->eliminarPodcast \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL PODCAST NO FUE ELIMINADA DE GESTORPORTAL.');
        }
        return $this->redirect()->toUrl('index-podcast?idPodcast=' . $programaOBJ->getIdPodcast());
    }
    public function activarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idPrograma = (int) $this->params()->fromQuery('idPrograma', 0);
            $diasSeleccionados = $this->DAO->getDias($idPrograma);
            $infoArchivo = $this->DAO->getProgramaDetalle($idPrograma);
            $form = new ProgramaForm('activar');
            $form->get('dias')->setValue($diasSeleccionados);
            $form->setData($infoArchivo);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new ProgramaForm('activar');
        $programaOBJ = new Programa();
        $form->setInputFilter($programaOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            //print_r($form->getMessages());
            //return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE ELIMINACION DEL PROGRAMA NO ES VALIDA');
            return $this->redirect()->toUrl('index?idPrograma=' . $this->params()->fromPost('idPrograma', 0));
        }
        //----------------------------------------------------------------------
        try {
            $programaOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $programaOBJ->setModificadopor($modificadopor);
            $programaOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->eliminarActivar($programaOBJ, 'Activo');
            $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE RECUPERADO DE GESTORPORTAL');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ACTIVAR PROGRAMA - EmisoraController->activar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL PROGRAMA NO FUE ELIMINADA DE GESTORPORTAL.');
        }
        return $this->redirect()->toUrl('index?idPrograma=' . $programaOBJ->getidPrograma());
    }
    public function activarPodcastAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idPodcast = (int) $this->params()->fromQuery('idPodcast', 0);
            $infoLven = $this->DAO->getPodcastDetalle($idPodcast);
            $form = new PodcastForm('activar-podcast');
            $form->bind($infoLven);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new PodcastForm('activar');
        $programaOBJ = new Podcast();
        $form->setInputFilter($programaOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            //print_r($form->getMessages());
            //return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE ELIMINACION DEL PODCAST NO ES VALIDA');
            return $this->redirect()->toUrl('index-podcast?idPodcast=' . $this->params()->fromPost('idPodcast', 0));
        }
        //----------------------------------------------------------------------
        try {
            $programaOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $programaOBJ->setModificadopor($modificadopor);
            $programaOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->eliminarActivarPodcast($programaOBJ, 'Activo');
            $this->flashMessenger()->addSuccessMessage('EL PODCAST FUE RECUPERADO DE GESTORPORTAL');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ELIMINAR PODCAST - EmisoraController->activarPodcast \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL PODCAST NO FUE ELIMINADA DE GESTORPORTAL.');
        }
        return $this->redirect()->toUrl('index-podcast?idPodcast=' . $programaOBJ->getIdPodcast());
    }
    //------------------------------------------------------------------------------  
    public function actualizarimagenAction()
    {
        $idPrograma = (int) $this->params()->fromQuery('idPrograma', 0);
        $form = $this->getFormulario('actualizarimagen', $idPrograma);
        $diasSeleccionados = $this->DAO->getDias($idPrograma);
        $form->get('dias')->setValue($diasSeleccionados);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $session = $this->getInfoSesion();
                $emisoraOBJ = new Programa($form->getData());
                $dias = $this->params()->fromPost('dias', []);
                //----------------------------------------------------------------------
                $files = $request->getFiles()->toArray();
                $respaldoImagen = $this->procesarArchivo($files, 'imagen', ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'], '250B', '500KB', 'programas/PROGRAMA', 'sinImagen.png');
                if ($respaldoImagen === false) return $this->redirect()->toUrl('index');
                //----------------------------------------------------------------------
                $emisoraOBJ->setImagen($respaldoImagen);
                $emisoraOBJ->setModificadopor($session['login']);
                $emisoraOBJ->setFechahoramod(date('Y-m-d H:i:s'));
                try {
                    $this->DAO->editar($emisoraOBJ, $dias);
                    $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE EDITADO EN GESTORPORTAL');
                    return $this->redirect()->toUrl('index');
                } catch (\Exception $ex) {
                    $msgLog = "\n" . date('Y-m-d H:i:s') . " ACTUALIZAR IMAGEN - EmisoraController->actualizarimagen \n"
                        . $ex->getMessage()
                        . "\n----------------------------------------------------------------------- \n";
                    $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
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
    public function actualizarimagenPodcastAction()
    {
        $idPodcast = (int) $this->params()->fromQuery('idPodcast', 0);
        $form = $this->getFormularioPodcast('actualizarimagen-podcast', $idPodcast);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $session = $this->getInfoSesion();
                $emisoraOBJ = new Podcast($form->getData());
                //----------------------------------------------------------------------
                $files = $request->getFiles()->toArray();
                $respaldoImagen = $this->procesarArchivo($files, 'imagen', ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'], '250B', '500KB', 'podcast/PODCAST', 'sinImagen.png');
                if ($respaldoImagen === false) return $this->redirect()->toUrl('index-podcast');
                //----------------------------------------------------------------------
                $emisoraOBJ->setImagen($respaldoImagen);
                $emisoraOBJ->setModificadopor($session['login']);
                $emisoraOBJ->setFechahoramod(date('Y-m-d H:i:s'));
                try {
                    $this->DAO->editarPodcast($emisoraOBJ);
                    $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE EDITADO EN GESTORPORTAL');
                    return $this->redirect()->toUrl('index-podcast');
                } catch (\Exception $ex) {
                    $msgLog = "\n" . date('Y-m-d H:i:s') . " ACTUALIZARIMAGENPODCAST Archivo - EmisoraController->actualizarimagenPodcast \n"
                        . $ex->getMessage()
                        . "\n----------------------------------------------------------------------- \n";
                    $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
                    fwrite($file, $msgLog);
                    fclose($file);
                    $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL ARCHIVO NO FUE EDITADO EN GESTORPORTAL.');
                }
            } else {
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE, EL ARCHIVO NO FUE EDITADO EN GESTORPORTAL');
                return $this->redirect()->toUrl('index-podcast');
            }
        }
        $view = new ViewModel([
            'form' => $form,
        ]);
        $view->setTerminal(true);
        return $view;
    }
    public function actualizaraudioPodcastAction()
    {
        $idPodcast = (int) $this->params()->fromQuery('idPodcast', 0);
        $form = $this->getFormularioPodcast('actualizaraudio-podcast', $idPodcast);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $session = $this->getInfoSesion();
                $emisoraOBJ = new Podcast($form->getData());
                //----------------------------------------------------------------------
                $files = $request->getFiles()->toArray();
                $respaldoAudio = $this->procesarArchivo($files, 'audio_url', ['mp3', 'wav', 'aac'], '1KB', '25MB', 'audios/PODCAST', 'sinAudio.mp3');
                if ($respaldoAudio === false) return $this->redirect()->toUrl('index-podcast');
                //----------------------------------------------------------------------
                $emisoraOBJ->setAudio_url($respaldoAudio);
                $emisoraOBJ->setModificadopor($session['login']);
                $emisoraOBJ->setFechahoramod(date('Y-m-d H:i:s'));
                try {
                    $this->DAO->editarPodcast($emisoraOBJ);
                    $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE EDITADO EN GESTORPORTAL');
                    return $this->redirect()->toUrl('index-podcast');
                } catch (\Exception $ex) {
                    $msgLog = "\n" . date('Y-m-d H:i:s') . " ACTUALIZARIMAGENPODCAST Archivo - EmisoraController->actualizarimagenPodcast \n"
                        . $ex->getMessage()
                        . "\n----------------------------------------------------------------------- \n";
                    $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
                    fwrite($file, $msgLog);
                    fclose($file);
                    $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL ARCHIVO NO FUE EDITADO EN GESTORPORTAL.');
                }
            } else {
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE, EL ARCHIVO NO FUE EDITADO EN GESTORPORTAL');
                return $this->redirect()->toUrl('index-podcast');
            }
        }
        $view = new ViewModel([
            'form' => $form,
        ]);
        $view->setTerminal(true);
        return $view;
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

    private function procesarArchivoNew(
        array $files,
        string $nombreCampo,
        array $extensionesPermitidas,
        string $tamanoMin,
        string $tamanoMax,
        string $directorio,
        string $default
    ) {
        // ¿Se envió archivo?
        if (!isset($files[$nombreCampo]) || $files[$nombreCampo]['error'] === UPLOAD_ERR_NO_FILE) {
            return $default;
        }

        // ---------------- VALIDACIONES ----------------
        $uploadOK = new \Laminas\Validator\File\UploadFile();
        if (!$uploadOK->isValid($files[$nombreCampo])) {
            $this->flashMessenger()->addErrorMessage("ERROR AL CARGAR EL ARCHIVO DE {$nombreCampo}");
            return false;
        }

        $sizeValidator = new \Laminas\Validator\File\Size([
            'min' => $tamanoMin,
            'max' => $tamanoMax
        ]);
        if (!$sizeValidator->isValid($files[$nombreCampo])) {
            $this->flashMessenger()->addErrorMessage(
                "ARCHIVO DE {$nombreCampo} FUERA DE RANGO ({$tamanoMin} a {$tamanoMax})"
            );
            return false;
        }

        $extValidator = new \Laminas\Validator\File\Extension([
            'extension' => $extensionesPermitidas
        ]);
        if (!$extValidator->isValid($files[$nombreCampo])) {
            $this->flashMessenger()->addErrorMessage(
                "EXTENSIÓN NO PERMITIDA PARA {$nombreCampo}"
            );
            return false;
        }

        // ---------------- NOMBRE FINAL ----------------
        $ext = pathinfo($files[$nombreCampo]['name'], PATHINFO_EXTENSION);
        $nombreFinal = $directorio . '_' . time() . '_' . uniqid() . '.' . $ext;

        // ---------------- CONEXIÓN FTP ----------------
        $ftp = ftp_connect($this->ftpHost);
        if (!$ftp) {
            $this->flashMessenger()->addErrorMessage('NO SE PUDO CONECTAR AL SERVIDOR FTP');
            return false;
        }

        if (!ftp_login($ftp, $this->ftpUser, $this->ftpPass)) {
            ftp_close($ftp);
            $this->flashMessenger()->addErrorMessage('ERROR DE AUTENTICACIÓN FTP');
            return false;
        }

        if ($this->ftpPassive) {
            ftp_pasv($ftp, true);
        }

        // ---------------- RUTA REMOTA ----------------
        $rutaRemota = rtrim($this->ftpBasePath, '/') . '/' . trim($directorio, '/') . '/';

        // Crear directorio si no existe
        @ftp_mkdir($ftp, $rutaRemota);

        $archivoRemoto = $rutaRemota . $nombreFinal;

        // ---------------- SUBIDA ----------------
        $tmpFile = $files[$nombreCampo]['tmp_name'];

        if (!ftp_put($ftp, $archivoRemoto, $tmpFile, FTP_BINARY)) {
            ftp_close($ftp);
            $this->flashMessenger()->addErrorMessage(
                "NO SE PUDO SUBIR EL ARCHIVO {$nombreCampo} AL FTP"
            );
            return false;
        }

        ftp_close($ftp);

        // 👉 Retornas lo que guardarás en BD
        // Puede ser:
        // - ruta FTP
        // - o URL pública si existe
        return $archivoRemoto;
    }


    private function registrarLogError($accion, $excepcion)
    {
        $msgLog = "\n" . date('Y-m-d H:i:s') . " {$accion} - EmisoraController->registrarPodcast \n"
            . $excepcion->getMessage()
            . "\n----------------------------------------------------------------------- \n";
        file_put_contents($this->rutaLog . 'gestorportal.log', $msgLog, FILE_APPEND);
    }
    //------------------------------------------------------------------------------  
}
