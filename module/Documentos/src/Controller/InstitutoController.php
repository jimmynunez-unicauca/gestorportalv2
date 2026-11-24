<?php

declare(strict_types=1);

namespace Documentos\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Documentos\Modelo\DAO\InstitutoDAO;
use Documentos\Formularios\InstitutoForm;
use Documentos\Modelo\Entidades\Instituto;

class InstitutoController extends AbstractActionController
{
    private $DAO;
    private $rutaLog = './public/log/';

    public function __construct(InstitutoDAO $dao)
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
        $msgLog = "\n" . date('Y-m-d H:i:s') . " {$accion} - InstitutoController \n"
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
        $form = new InstitutoForm('registrar', $this->DAO->getCiudadesSelect());
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $institutoOBJ = new Instituto();
        $form->setInputFilter($institutoOBJ->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            $this->flashMessenger()->addErrorMessage(
                'INFORMACIÓN NO VÁLIDA: ' . json_encode($form->getMessages(), JSON_UNESCAPED_UNICODE)
            );
            return $this->redirect()->toUrl('index');
        }

        $institutoOBJ->exchangeArray($form->getData());
        $institutoOBJ->setEstado('Activo');
        $institutoOBJ->setRegistradopor($infosesion['login']);
        $institutoOBJ->setModificadopor('');
        $institutoOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $institutoOBJ->setFechahoramod('0000-00-00 00:00:00');

        try {
            $this->DAO->registrar($institutoOBJ);
            $this->flashMessenger()->addSuccessMessage('LA INSTITUCIÓN FUE REGISTRADA');
        } catch (\Exception $ex) {
            $this->registrarLogError('REGISTRAR INSTITUCION', $ex);
            $this->flashMessenger()->addErrorMessage('LA INSTITUCIÓN NO FUE REGISTRADA');
        }
        return $this->redirect()->toUrl('index');
    }

    //==========================================================================
    public function editarAction()
    {
        $request = $this->getRequest();
        $idInstituto = $request->isPost()
            ? (int) $request->getPost('idInstituto', 0)
            : (int) $this->params()->fromQuery('idInstituto', 0);

        if ($idInstituto <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE INSTITUCIÓN INVÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        $institutoOBJ = $this->DAO->getInstituto($idInstituto);
        if (!$institutoOBJ) {
            $this->flashMessenger()->addErrorMessage('LA INSTITUCIÓN NO EXISTE');
            return $this->redirect()->toUrl('index');
        }

        $form = new InstitutoForm('editar', $this->DAO->getCiudadesSelect());
        $form->bind($institutoOBJ);

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
        $institutoOBJ->setModificadopor($session['login']);
        $institutoOBJ->setFechahoramod(date('Y-m-d H:i:s'));

        try {
            $this->DAO->editar($institutoOBJ);
            $this->flashMessenger()->addSuccessMessage('LA INSTITUCIÓN FUE EDITADA');
        } catch (\Exception $ex) {
            $this->registrarLogError('EDITAR INSTITUCION', $ex);
            $this->flashMessenger()->addErrorMessage('LA INSTITUCIÓN NO FUE EDITADA');
        }
        return $this->redirect()->toUrl('index');
    }

    //==========================================================================
    public function detalleAction()
    {
        $idInstituto = (int) $this->params()->fromQuery('idInstituto', 0);
        $info = $this->DAO->getInstitutoDetalle($idInstituto);
        if (!$info) {
            $this->flashMessenger()->addErrorMessage('LA INSTITUCIÓN NO EXISTE');
            return $this->redirect()->toUrl('index');
        }
        return (new ViewModel(['form' => $info]))->setTerminal(true);
    }

    //==========================================================================
    public function desactivarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $idInstituto = (int) $this->params()->fromQuery('idInstituto', 0);
            $info = $this->DAO->getInstitutoDetalle($idInstituto);
            if (!$info) {
                $this->flashMessenger()->addErrorMessage('LA INSTITUCIÓN NO EXISTE');
                return $this->redirect()->toUrl('index');
            }
            $form = new InstitutoForm('desactivar', $this->DAO->getCiudadesSelect());
            $form->setData($info);
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $idInstituto = (int) $request->getPost('idInstituto', 0);
        if ($idInstituto <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE INSTITUCIÓN INVÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        try {
            $session = $this->getInfoSesion();
            $institutoOBJ = new Instituto();
            $institutoOBJ->setIdInstituto($idInstituto);
            $institutoOBJ->setModificadopor($session['login']);
            $institutoOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->eliminarActivar($institutoOBJ, 'Eliminado');
            $this->flashMessenger()->addSuccessMessage('LA INSTITUCIÓN FUE DESACTIVADA');
        } catch (\Exception $ex) {
            $this->registrarLogError('DESACTIVAR INSTITUCION', $ex);
            $this->flashMessenger()->addErrorMessage('LA INSTITUCIÓN NO FUE DESACTIVADA');
        }
        return $this->redirect()->toUrl('index');
    }

    //==========================================================================
    public function activarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $idInstituto = (int) $this->params()->fromQuery('idInstituto', 0);
            $info = $this->DAO->getInstitutoDetalle($idInstituto);
            if (!$info) {
                $this->flashMessenger()->addErrorMessage('LA INSTITUCIÓN NO EXISTE');
                return $this->redirect()->toUrl('index');
            }
            $form = new InstitutoForm('activar', $this->DAO->getCiudadesSelect());
            $form->setData($info);
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $idInstituto = (int) $request->getPost('idInstituto', 0);
        if ($idInstituto <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE INSTITUCIÓN INVÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        try {
            $session = $this->getInfoSesion();
            $institutoOBJ = new Instituto();
            $institutoOBJ->setIdInstituto($idInstituto);
            $institutoOBJ->setModificadopor($session['login']);
            $institutoOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->eliminarActivar($institutoOBJ, 'Activo');
            $this->flashMessenger()->addSuccessMessage('LA INSTITUCIÓN FUE ACTIVADA');
        } catch (\Exception $ex) {
            $this->registrarLogError('ACTIVAR INSTITUCION', $ex);
            $this->flashMessenger()->addErrorMessage('LA INSTITUCIÓN NO FUE ACTIVADA');
        }
        return $this->redirect()->toUrl('index');
    }

    //==========================================================================
    public function eliminarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $idInstituto = (int) $this->params()->fromQuery('idInstituto', 0);
            $info = $this->DAO->getInstitutoDetalle($idInstituto);
            if (!$info) {
                $this->flashMessenger()->addErrorMessage('LA INSTITUCIÓN NO EXISTE');
                return $this->redirect()->toUrl('index');
            }
            $form = new InstitutoForm('eliminar', $this->DAO->getCiudadesSelect());
            $form->setData($info);
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $idInstituto = (int) $request->getPost('idInstituto', 0);
        if ($idInstituto <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE INSTITUCIÓN INVÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        try {
            $this->DAO->borrarInstituto($idInstituto);
            $this->flashMessenger()->addSuccessMessage('LA INSTITUCIÓN FUE ELIMINADA DEFINITIVAMENTE');
        } catch (\Exception $ex) {
            $this->registrarLogError('ELIMINAR INSTITUCION', $ex);
            $this->flashMessenger()->addErrorMessage('LA INSTITUCIÓN NO FUE ELIMINADA');
        }
        return $this->redirect()->toUrl('index');
    }
}
