<?php

declare(strict_types=1);

namespace Documentos\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Documentos\Modelo\DAO\CiudadDAO;
use Documentos\Formularios\CiudadForm;
use Documentos\Modelo\Entidades\Ciudad;

class CiudadController extends AbstractActionController
{
    private $DAO;
    private $rutaLog = './public/log/';

    public function __construct(CiudadDAO $dao)
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
        $msgLog = "\n" . date('Y-m-d H:i:s') . " {$accion} - CiudadController \n"
            . $excepcion->getMessage()
            . "\n----------------------------------------------------------------------- \n";
        file_put_contents($this->rutaLog . 'gestorportal.log', $msgLog, FILE_APPEND);
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
        $form = new CiudadForm('registrar', $this->DAO->getCountriesSelect());
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $ciudadOBJ = new Ciudad();
        $form->setInputFilter($ciudadOBJ->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            $this->flashMessenger()->addErrorMessage(
                'INFORMACIÓN NO VÁLIDA: ' . json_encode($form->getMessages(), JSON_UNESCAPED_UNICODE)
            );
            return $this->redirect()->toUrl('index');
        }

        $ciudadOBJ->exchangeArray($form->getData());
        $ciudadOBJ->setEstado('Activo');
        $ciudadOBJ->setRegistradopor($infosesion['login']);
        $ciudadOBJ->setModificadopor('');
        $ciudadOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $ciudadOBJ->setFechahoramod('0000-00-00 00:00:00');

        try {
            $this->DAO->registrar($ciudadOBJ);
            $this->flashMessenger()->addSuccessMessage('LA CIUDAD FUE REGISTRADA');
        } catch (\Exception $ex) {
            $this->registrarLogError('REGISTRAR CIUDAD', $ex);
            $this->flashMessenger()->addErrorMessage('LA CIUDAD NO FUE REGISTRADA');
        }
        return $this->redirect()->toUrl('index');
    }

    //==========================================================================
    public function editarAction()
    {
        $request = $this->getRequest();
        $idCiudades = $request->isPost()
            ? (int) $request->getPost('idCiudades', 0)
            : (int) $this->params()->fromQuery('idCiudades', 0);

        if ($idCiudades <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE CIUDAD INVÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        $ciudadOBJ = $this->DAO->getCiudad($idCiudades);
        if (!$ciudadOBJ) {
            $this->flashMessenger()->addErrorMessage('LA CIUDAD NO EXISTE');
            return $this->redirect()->toUrl('index');
        }

        $form = new CiudadForm('editar', $this->DAO->getCountriesSelect());
        $form->bind($ciudadOBJ);

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
        $ciudadOBJ->setModificadopor($session['login']);
        $ciudadOBJ->setFechahoramod(date('Y-m-d H:i:s'));

        try {
            $this->DAO->editar($ciudadOBJ);
            $this->flashMessenger()->addSuccessMessage('LA CIUDAD FUE EDITADA');
        } catch (\Exception $ex) {
            $this->registrarLogError('EDITAR CIUDAD', $ex);
            $this->flashMessenger()->addErrorMessage('LA CIUDAD NO FUE EDITADA');
        }
        return $this->redirect()->toUrl('index');
    }

    //==========================================================================
    public function detalleAction()
    {
        $idCiudades = (int) $this->params()->fromQuery('idCiudades', 0);
        $info = $this->DAO->getCiudadDetalle($idCiudades);
        if (!$info) {
            $this->flashMessenger()->addErrorMessage('LA CIUDAD NO EXISTE');
            return $this->redirect()->toUrl('index');
        }
        return (new ViewModel(['form' => $info]))->setTerminal(true);
    }

    //==========================================================================
    public function desactivarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $idCiudades = (int) $this->params()->fromQuery('idCiudades', 0);
            $info = $this->DAO->getCiudadDetalle($idCiudades);
            if (!$info) {
                $this->flashMessenger()->addErrorMessage('LA CIUDAD NO EXISTE');
                return $this->redirect()->toUrl('index');
            }
            $form = new CiudadForm('desactivar', $this->DAO->getCountriesSelect());
            $form->setData($info);
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $idCiudades = (int) $request->getPost('idCiudades', 0);
        if ($idCiudades <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE CIUDAD INVÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        try {
            $session = $this->getInfoSesion();
            $ciudadOBJ = new Ciudad();
            $ciudadOBJ->setIdCiudades($idCiudades);
            $ciudadOBJ->setModificadopor($session['login']);
            $ciudadOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->eliminarActivar($ciudadOBJ, 'Eliminado');
            $this->flashMessenger()->addSuccessMessage('LA CIUDAD FUE DESACTIVADA');
        } catch (\Exception $ex) {
            $this->registrarLogError('DESACTIVAR CIUDAD', $ex);
            $this->flashMessenger()->addErrorMessage('LA CIUDAD NO FUE DESACTIVADA');
        }
        return $this->redirect()->toUrl('index');
    }

    //==========================================================================
    public function activarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $idCiudades = (int) $this->params()->fromQuery('idCiudades', 0);
            $info = $this->DAO->getCiudadDetalle($idCiudades);
            if (!$info) {
                $this->flashMessenger()->addErrorMessage('LA CIUDAD NO EXISTE');
                return $this->redirect()->toUrl('index');
            }
            $form = new CiudadForm('activar', $this->DAO->getCountriesSelect());
            $form->setData($info);
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $idCiudades = (int) $request->getPost('idCiudades', 0);
        if ($idCiudades <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE CIUDAD INVÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        try {
            $session = $this->getInfoSesion();
            $ciudadOBJ = new Ciudad();
            $ciudadOBJ->setIdCiudades($idCiudades);
            $ciudadOBJ->setModificadopor($session['login']);
            $ciudadOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->eliminarActivar($ciudadOBJ, 'Activo');
            $this->flashMessenger()->addSuccessMessage('LA CIUDAD FUE ACTIVADA');
        } catch (\Exception $ex) {
            $this->registrarLogError('ACTIVAR CIUDAD', $ex);
            $this->flashMessenger()->addErrorMessage('LA CIUDAD NO FUE ACTIVADA');
        }
        return $this->redirect()->toUrl('index');
    }

    //==========================================================================
    public function eliminarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $idCiudades = (int) $this->params()->fromQuery('idCiudades', 0);
            $info = $this->DAO->getCiudadDetalle($idCiudades);
            if (!$info) {
                $this->flashMessenger()->addErrorMessage('LA CIUDAD NO EXISTE');
                return $this->redirect()->toUrl('index');
            }
            $form = new CiudadForm('eliminar', $this->DAO->getCountriesSelect());
            $form->setData($info);
            return (new ViewModel([
                'form'             => $form,
                'tieneInstitutos'  => $this->DAO->tieneInstitutos($idCiudades),
            ]))->setTerminal(true);
        }

        $idCiudades = (int) $request->getPost('idCiudades', 0);
        if ($idCiudades <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE CIUDAD INVÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        if ($this->DAO->tieneInstitutos($idCiudades)) {
            $this->flashMessenger()->addErrorMessage(
                'NO SE PUEDE BORRAR: LA CIUDAD TIENE INSTITUCIONES ASOCIADAS.'
            );
            return $this->redirect()->toUrl('index');
        }

        try {
            $this->DAO->borrarCiudad($idCiudades);
            $this->flashMessenger()->addSuccessMessage('LA CIUDAD FUE ELIMINADA DEFINITIVAMENTE');
        } catch (\Exception $ex) {
            $this->registrarLogError('ELIMINAR CIUDAD', $ex);
            $this->flashMessenger()->addErrorMessage('LA CIUDAD NO FUE ELIMINADA');
        }
        return $this->redirect()->toUrl('index');
    }
}
