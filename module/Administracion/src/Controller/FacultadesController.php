<?php

declare(strict_types=1);

namespace Administracion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Administracion\Modelo\DAO\FacultadesDAO;
use Administracion\Formularios\FacultadForm;
use Administracion\Modelo\Entidades\Facultad;

class FacultadesController extends AbstractActionController
{
    private $DAO;
    private $rutaLog = './public/log/';

    public function __construct(FacultadesDAO $dao)
    {
        $this->DAO = $dao;
    }

    private function registrarLogError($accion, $excepcion)
    {
        $msg = "\n" . date('Y-m-d H:i:s') . " {$accion} - FacultadesController \n"
            . $excepcion->getMessage()
            . "\n----------------------------------------------------------------------- \n";
        @file_put_contents($this->rutaLog . 'gestorportal.log', $msg, FILE_APPEND);
    }

    public function indexAction()
    {
        return new ViewModel(['fetchAll' => $this->DAO->fetchAll('')]);
    }

    public function registrarAction()
    {
        $form = new FacultadForm('registrar');
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $obj = new Facultad();
        $form->setInputFilter($obj->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            $this->flashMessenger()->addErrorMessage(
                'INFORMACIÓN NO VÁLIDA: ' . json_encode($form->getMessages(), JSON_UNESCAPED_UNICODE)
            );
            return $this->redirect()->toUrl('index');
        }

        $obj->exchangeArray($form->getData());

        try {
            $this->DAO->registrar($obj);
            $this->flashMessenger()->addSuccessMessage('LA FACULTAD FUE REGISTRADA');
        } catch (\Exception $ex) {
            $this->registrarLogError('REGISTRAR FACULTAD', $ex);
            $this->flashMessenger()->addErrorMessage('LA FACULTAD NO FUE REGISTRADA');
        }
        return $this->redirect()->toUrl('index');
    }

    public function editarAction()
    {
        $request = $this->getRequest();
        $id = $request->isPost()
            ? (int) $request->getPost('idFacultad', 0)
            : (int) $this->params()->fromQuery('idFacultad', 0);

        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID INVÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        $obj = $this->DAO->getFacultad($id);
        if (!$obj) {
            $this->flashMessenger()->addErrorMessage('LA FACULTAD NO EXISTE');
            return $this->redirect()->toUrl('index');
        }

        $form = new FacultadForm('editar');
        $form->bind($obj);

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

        try {
            $this->DAO->editar($obj);
            $this->flashMessenger()->addSuccessMessage('LA FACULTAD FUE EDITADA');
        } catch (\Exception $ex) {
            $this->registrarLogError('EDITAR FACULTAD', $ex);
            $this->flashMessenger()->addErrorMessage('LA FACULTAD NO FUE EDITADA');
        }
        return $this->redirect()->toUrl('index');
    }

    public function detalleAction()
    {
        $id = (int) $this->params()->fromQuery('idFacultad', 0);
        $info = $this->DAO->getFacultadDetalle($id);
        if (!$info) {
            $this->flashMessenger()->addErrorMessage('LA FACULTAD NO EXISTE');
            return $this->redirect()->toUrl('index');
        }
        return (new ViewModel(['form' => $info]))->setTerminal(true);
    }

    public function eliminarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('idFacultad', 0);
            $info = $this->DAO->getFacultadDetalle($id);
            if (!$info) {
                $this->flashMessenger()->addErrorMessage('LA FACULTAD NO EXISTE');
                return $this->redirect()->toUrl('index');
            }
            $form = new FacultadForm('eliminar');
            $form->setData($info);
            return (new ViewModel([
                'form'            => $form,
                'tieneProgramas'  => $this->DAO->tieneProgramas($id),
            ]))->setTerminal(true);
        }

        $id = (int) $request->getPost('idFacultad', 0);
        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID INVÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        if ($this->DAO->tieneProgramas($id)) {
            $this->flashMessenger()->addErrorMessage(
                'NO SE PUEDE BORRAR: LA FACULTAD TIENE PROGRAMAS ASOCIADOS.'
            );
            return $this->redirect()->toUrl('index');
        }

        try {
            $this->DAO->borrarFacultad($id);
            $this->flashMessenger()->addSuccessMessage('LA FACULTAD FUE ELIMINADA');
        } catch (\Exception $ex) {
            $this->registrarLogError('ELIMINAR FACULTAD', $ex);
            $this->flashMessenger()->addErrorMessage('LA FACULTAD NO FUE ELIMINADA');
        }
        return $this->redirect()->toUrl('index');
    }
}
