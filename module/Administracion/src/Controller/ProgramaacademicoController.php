<?php

declare(strict_types=1);

namespace Administracion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Administracion\Modelo\DAO\ProgramaacademicoDAO;
use Administracion\Modelo\DAO\FacultadesDAO;
use Administracion\Formularios\ProgramaacademicoForm;
use Administracion\Modelo\Entidades\Programaacademico;

class ProgramaacademicoController extends AbstractActionController
{
    private $DAO;
    private $facultadesDAO;
    private $rutaLog = './public/log/';

    public function __construct(ProgramaacademicoDAO $dao, FacultadesDAO $facultadesDAO)
    {
        $this->DAO = $dao;
        $this->facultadesDAO = $facultadesDAO;
    }

    private function registrarLogError($accion, $excepcion)
    {
        $msg = "\n" . date('Y-m-d H:i:s') . " {$accion} - ProgramaacademicoController \n"
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
        $form = new ProgramaacademicoForm('registrar', $this->facultadesDAO->getFacultadesSelect());
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $obj = new Programaacademico();
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
            $this->flashMessenger()->addSuccessMessage('EL PROGRAMA FUE REGISTRADO');
        } catch (\Exception $ex) {
            $this->registrarLogError('REGISTRAR PROGRAMA', $ex);
            $this->flashMessenger()->addErrorMessage('EL PROGRAMA NO FUE REGISTRADO');
        }
        return $this->redirect()->toUrl('index');
    }

    public function editarAction()
    {
        $request = $this->getRequest();
        $id = $request->isPost()
            ? (int) $request->getPost('idPrograma', 0)
            : (int) $this->params()->fromQuery('idPrograma', 0);

        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID INVÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        $obj = $this->DAO->getPrograma($id);
        if (!$obj) {
            $this->flashMessenger()->addErrorMessage('EL PROGRAMA NO EXISTE');
            return $this->redirect()->toUrl('index');
        }

        $form = new ProgramaacademicoForm('editar', $this->facultadesDAO->getFacultadesSelect());
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
            $this->flashMessenger()->addSuccessMessage('EL PROGRAMA FUE EDITADO');
        } catch (\Exception $ex) {
            $this->registrarLogError('EDITAR PROGRAMA', $ex);
            $this->flashMessenger()->addErrorMessage('EL PROGRAMA NO FUE EDITADO');
        }
        return $this->redirect()->toUrl('index');
    }

    public function detalleAction()
    {
        $id = (int) $this->params()->fromQuery('idPrograma', 0);
        $info = $this->DAO->getProgramaDetalle($id);
        if (!$info) {
            $this->flashMessenger()->addErrorMessage('EL PROGRAMA NO EXISTE');
            return $this->redirect()->toUrl('index');
        }
        return (new ViewModel(['form' => $info]))->setTerminal(true);
    }

    public function eliminarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $id = (int) $this->params()->fromQuery('idPrograma', 0);
            $info = $this->DAO->getProgramaDetalle($id);
            if (!$info) {
                $this->flashMessenger()->addErrorMessage('EL PROGRAMA NO EXISTE');
                return $this->redirect()->toUrl('index');
            }
            $form = new ProgramaacademicoForm('eliminar', []);
            $form->setData($info);
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $id = (int) $request->getPost('idPrograma', 0);
        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID INVÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        try {
            $this->DAO->borrarPrograma($id);
            $this->flashMessenger()->addSuccessMessage('EL PROGRAMA FUE ELIMINADO');
        } catch (\Exception $ex) {
            $this->registrarLogError('ELIMINAR PROGRAMA', $ex);
            $this->flashMessenger()->addErrorMessage('EL PROGRAMA NO FUE ELIMINADO');
        }
        return $this->redirect()->toUrl('index');
    }
}
