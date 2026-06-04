<?php

declare(strict_types=1);

namespace Administracion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Administracion\Modelo\DAO\ComarcaDAO;
use Administracion\Formularios\ComarcaForm;
use Administracion\Modelo\Entidades\Comarca;

class ComarcaController extends AbstractActionController
{

    private $DAO;
    private $rutaLog = './public/log/';
    private $rutaArchivos = '/var/www/html/newportal/archivos/podcast_comarca/';
    /* private $rutaArchivos = './../archivos/podcast_comarca/'; */
    //------------------------------------------------------------------------------

    public function __construct(ComarcaDAO $dao)
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
    function getFormulario($action = '', $idPodcastComarca = 0)
    {
        $form = new ComarcaForm($action);
        if ($idPodcastComarca != 0) {
            $comarcaOBJ = $this->DAO->getPodcast($idPodcastComarca);
            $form->bind($comarcaOBJ);
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
        //----------------------------------------------------------------------
        $form = new ComarcaForm('registrar');
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $view = new ViewModel(['form' => $form]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $comarcaOBJ = new Comarca();
        $form->setInputFilter($comarcaOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            /* print_r($form->getMessages());
            return ['form' => $form]; */
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL PROGRAMA NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }
        //----------------------------------------------------------------------
        $files = $request->getFiles()->toArray();
        $respaldoImagen = $this->procesarArchivo($files, 'imagen', ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'], '250B', '300KB', 'imagenes/COMARCA', 'sinImagen.png');
        if ($respaldoImagen === false) return $this->redirect()->toUrl('index');
        //----------------------------------------------------------------------
        $comarcaOBJ->exchangeArray($form->getData());
        $comarcaOBJ->setImagen($respaldoImagen);
        $comarcaOBJ->setEstado('Activo');
        $comarcaOBJ->setRegistradopor($registradopor);
        $comarcaOBJ->setModificadopor('');
        $comarcaOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $comarcaOBJ->setFechahoramod('0000-00-00 00:00:00');
        try {
            $this->DAO->registrar($comarcaOBJ);
            $this->flashMessenger()->addSuccessMessage('EL PODCAST FUE REGISTRADO EN CoMarca');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " REGISTRAR PROGRAMA - ComarcaController->registrar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! EL PODCAST NO FUE REGISTRADO EN GESTORPORTAL.');
        }
        return $this->redirect()->toUrl('index');
    }



    //------------------------------------------------------------------------------  
    public function editarAction()
    {
        $idPodcastComarca = (int) $this->params()->fromQuery('idPodcastComarca', 0);
        $form = $this->getFormulario('editar', $idPodcastComarca);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $session = $this->getInfoSesion();
                $comarcaOBJ = new Comarca($form->getData());
                $comarcaOBJ->setModificadopor($session['login']);
                $comarcaOBJ->setFechahoramod(date('Y-m-d H:i:s'));
                try {
                    $this->DAO->editar($comarcaOBJ);
                    $this->flashMessenger()->addSuccessMessage('EL PODCAST FUE EDITADO EN GESTORPORTAL');
                    return $this->redirect()->toUrl('index');
                } catch (\Exception $ex) {
                    $msgLog = "\n" . date('Y-m-d H:i:s') . " EDITAR PODCAST - ComarcaController->editar \n"
                        . $ex->getMessage()
                        . "\n----------------------------------------------------------------------- \n";
                    $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
                    fwrite($file, $msgLog);
                    fclose($file);
                    $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL PODCAST NO FUE EDITADO EN GESTORPORTAL.');
                }
            } else {
                $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE, EL PODCAST NO FUE EDITADO EN GESTORPORTAL');
                return $this->redirect()->toUrl('index');
            }
        }
        $view = new ViewModel(['form' => $form]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------  
    public function detalleAction()
    {
        $idPodcastComarca = (int) $this->params()->fromQuery('idPodcastComarca', 0);
        $infoComarca = $this->DAO->getPodcastDetalle($idPodcastComarca);
        $view = new ViewModel(['form' => $infoComarca]);
        $view->setTerminal(true);
        return $view;
    }
    //------------------------------------------------------------------------------
    public function eliminarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idPodcastComarca = (int) $this->params()->fromQuery('idPodcastComarca', 0);
            $infoLven = $this->DAO->getPodcastDetalle($idPodcastComarca);
            $form = new ComarcaForm('eliminar');
            $form->setData($infoLven);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new ComarcaForm('eliminar');
        $comarcaOBJ = new Comarca();
        $form->setInputFilter($comarcaOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            //print_r($form->getMessages());
            //return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE ELIMINACION DEL PODCAST NO ES VALIDA');
            return $this->redirect()->toUrl('index?idPodcastComarca=' . $this->params()->fromPost('idPodcastComarca', 0));
        }
        //----------------------------------------------------------------------
        try {
            $comarcaOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $comarcaOBJ->setModificadopor($modificadopor);
            $comarcaOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->eliminarActivar($comarcaOBJ, 'Eliminado');
            $this->flashMessenger()->addSuccessMessage('EL PODCAST FUE ELIMINADA DE GESTORPORTAL');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ELIMINAR PODCAST - ComarcaController->eliminar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL PODCAST NO FUE ELIMINADA DE GESTORPORTAL.');
        }
        return $this->redirect()->toUrl('index?idPodcastComarca=' . $comarcaOBJ->getIdPodcastComarca());
    }

    public function activarAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $idPodcastComarca = (int) $this->params()->fromQuery('idPodcastComarca', 0);
            $infoArchivo = $this->DAO->getPodcastDetalle($idPodcastComarca);
            $form = new ComarcaForm('activar');
            $form->setData($infoArchivo);
            $view = new ViewModel([
                'form' => $form,
            ]);
            $view->setTerminal(true);
            return $view;
        }
        //----------------------------------------------------------------------
        $form = new ComarcaForm('activar');
        $comarcaOBJ = new Comarca();
        $form->setInputFilter($comarcaOBJ->getInputFilter());
        $form->setData($request->getPost());
        if (!$form->isValid()) {
            //print_r($form->getMessages());
            //return ['form' => $form];
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE ELIMINACION DEL PODCAST NO ES VALIDA');
            return $this->redirect()->toUrl('index?idPodcastComarca=' . $this->params()->fromPost('idPodcastComarca', 0));
        }
        //----------------------------------------------------------------------
        try {
            $comarcaOBJ->exchangeArray($form->getData());
            $infosesion = $this->getInfoSesion();
            $modificadopor = $infosesion['login'];
            $comarcaOBJ->setModificadopor($modificadopor);
            $comarcaOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->eliminarActivar($comarcaOBJ, 'Activo');
            $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE RECUPERADO DE GESTORPORTAL');
        } catch (\Exception $ex) {
            $msgLog = "\n" . date('Y-m-d H:i:s') . " ACTIVAR PROGRAMA - ComarcaController->activar \n"
                . $ex->getMessage()
                . "\n----------------------------------------------------------------------- \n";
            $file = fopen($this->rutaLog . 'gestorportal.log', 'a');
            fwrite($file, $msgLog);
            fclose($file);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! <br>EL PROGRAMA NO FUE ELIMINADA DE GESTORPORTAL.');
        }
        return $this->redirect()->toUrl('index?idPodcastComarca=' . $comarcaOBJ->getidPodcastComarca());
    }
    //------------------------------------------------------------------------------  
    public function actualizarimagenAction()
    {
        $idPodcastComarca = (int) $this->params()->fromQuery('idPodcastComarca', 0);
        $form = $this->getFormulario('actualizarimagen', $idPodcastComarca);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $session = $this->getInfoSesion();
                $comarcaOBJ = new Comarca($form->getData());
                //----------------------------------------------------------------------
                $files = $request->getFiles()->toArray();
                $respaldoImagen = $this->procesarArchivo($files, 'imagen', ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'], '250B', '300KB', 'imagenes/COMARCA', 'sinImagen.png');
                if ($respaldoImagen === false) return $this->redirect()->toUrl('index');
                //----------------------------------------------------------------------
                $comarcaOBJ->setImagen($respaldoImagen);
                $comarcaOBJ->setModificadopor($session['login']);
                $comarcaOBJ->setFechahoramod(date('Y-m-d H:i:s'));
                try {
                    $this->DAO->editar($comarcaOBJ);
                    $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE EDITADO EN GESTORPORTAL');
                    return $this->redirect()->toUrl('index');
                } catch (\Exception $ex) {
                    $msgLog = "\n" . date('Y-m-d H:i:s') . " ACTUALIZAR IMAGEN - ComarcaController->actualizarimagen \n"
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
    //------------------------------------------------------------------------------  
}
