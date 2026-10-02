<?php

declare(strict_types=1);

namespace Administracion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Administracion\Modelo\DAO\UnisaludconsultaafiliadosDAO;

class UnisaludconsultaafiliadosController extends AbstractActionController
{
    private $DAO;
    private $rutaLog = './public/log/';

    public function __construct(UnisaludconsultaafiliadosDAO $dao)
    {
        $this->DAO = $dao;
    }

    public function getInfoSesion()
    {
        $infoSesion = ['idEmpleadoCliente' => 0, 'login' => 'SIN INICIO DE SESION'];
        $auth = new AuthenticationService();
        if ($auth->hasIdentity()) {
            $infoSesion['login']            = $auth->getIdentity()->login;
            $infoSesion['idEmpleadoCliente'] = $auth->getIdentity()->idEmpleadoCliente;
        }
        return $infoSesion;
    }

    /* ---------------- VISTA PRINCIPAL ---------------- */
    public function indexAction()
    {
        return new ViewModel(['infoSesion' => $this->getInfoSesion()]);
    }

    /* ---------------- ENDPOINTS JSON ---------------- */
    public function kpisAction()
    {
        return new JsonModel($this->DAO->getKPIs());
    }

    public function listarConsultasAction()
    {
        return new JsonModel(['data' => $this->DAO->getConsultas($this->filtros())]);
    }

    public function listarUsuariosAction()
    {
        return new JsonModel(['data' => $this->DAO->getUsuarios($this->filtros())]);
    }

    public function listarAuditoriaAction()
    {
        return new JsonModel(['data' => $this->DAO->getAuditoria($this->filtros())]);
    }

    public function listarAfiliadosAction()
    {
        return new JsonModel(['data' => $this->DAO->getAfiliadosAgrupados($this->filtros())]);
    }

    public function estadisticasAction()
    {
        return new JsonModel([
            'porDia'           => $this->DAO->getConsultasPorDia(),
            'porTipo'          => $this->DAO->getConsultasPorTipo(),
            'topPrestadores'   => $this->DAO->getTopPrestadores(10),
            'topAfiliados'     => $this->DAO->getTopAfiliados(10),
            'accionesAuditoria' => $this->DAO->getAccionesAuditoria(),
            'porHora'          => $this->DAO->getConsultasPorHora(),
        ]);
    }

    public function detalleUsuarioAction()
    {
        $id = (int) $this->params()->fromRoute('id1', 0);
        if (!$id) return new JsonModel(['error' => 'ID no proporcionado']);
        $data = $this->DAO->getDetalleUsuario($id);
        return $data ? new JsonModel($data) : new JsonModel(['error' => 'Usuario no encontrado']);
    }

    public function detalleAfiliadoAction()
    {
        $ident = (string) $this->params()->fromRoute('id1', '');
        if ($ident === '') return new JsonModel(['error' => 'Identificación no proporcionada']);
        $data = $this->DAO->getDetalleAfiliado($ident);
        return $data ? new JsonModel($data) : new JsonModel(['error' => 'Afiliado no encontrado']);
    }

    /* ---------------- HELPERS ---------------- */
    private function filtros(): array
    {
        $q = $this->getRequest()->getQuery();
        return [
            'usuario_email' => (string) $q->get('usuario_email', ''),
            'identificacion' => (string) $q->get('identificacion', ''),
            'tipo_afiliado' => (string) $q->get('tipo_afiliado', ''),
            'email'         => (string) $q->get('email', ''),
            'accion'        => (string) $q->get('accion', ''),
            'fecha_desde'   => (string) $q->get('fecha_desde', ''),
            'fecha_hasta'   => (string) $q->get('fecha_hasta', ''),
        ];
    }
}
