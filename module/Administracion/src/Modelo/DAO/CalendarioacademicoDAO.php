<?php

namespace Administracion\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Administracion\Modelo\Entidades\Calendarioacademico;

class CalendarioacademicoDAO extends AbstractTableGateway
{

    protected $table = 'calendario_academico';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'calendario_academico';
        $select = new Select($this->table);
        $select->columns([
            'idCalendarioAcademico',
            'tipo',
            'title',
            'descripcion',
            'start',
            'end',
            'color',
            'textColor',
            'allDay',
            'estado',
            'registradopor',
            'modificadopor',
            'fechahorareg',
            'fechahoramod',
        ]);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("calendario_academico.idCalendarioAcademico DESC")->limit(25);
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getCalendarioacademicoDetalle($idCalendarioAcademico = 0)
    {
        $select = new Select('calendario_academico');
        $select->columns(array(
            'idCalendarioAcademico',
            'tipo',
            'title',
            'descripcion',
            'start',
            'end',
            'color',
            'textColor',
            'allDay',
            'estado',
            'registradopor',
            'modificadopor',
            'fechahorareg',
            'fechahoramod',
        ))->where("calendario_academico.idCalendarioAcademico = $idCalendarioAcademico")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getCalendarioacademico($idCalendarioAcademico = 0)
    {
        return new Calendarioacademico($this->select(array('idCalendarioAcademico' => $idCalendarioAcademico))->current()->getArrayCopy());
    }
    //------------------------------------------------------------------------------

    public function registrar(Calendarioacademico $CalendarioOBJ = null)
    {
        try {
            $this->table = 'calendario_academico';
            $insert = new Insert($this->table);
            $datos = $CalendarioOBJ->getArrayCopy();
            unset($datos['idCalendarioAcademico']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function editar(Calendarioacademico $CalendarioOBJ = null)
    {
        try {
            $this->table = 'calendario_academico';
            $idCalendarioAcademico = (int) $CalendarioOBJ->getIdCalendarioacademico();
            $update = new Update($this->table);
            $datos = $CalendarioOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("calendario_academico.idCalendarioAcademico =  $idCalendarioAcademico");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function eliminar($idCalendarioAcademico = 0, $registradopor = '')
    {
        try {
            $this->table = "calendario_academico";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Eliminado',
                'modificadopor' => $registradopor,
                'fechahoramod' => date('Y-m-d H:i:s'),
            ]);
            $update->where("calendario_academico.idCalendarioAcademico = $idCalendarioAcademico");
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    //------------------------------------------------------------------------------
    public function moverevento($idCalendarioAcademico = 0, $start = '', $end = '', $registradopor = '')
    {
        try {
            $this->table = 'calendario_academico';
            $update = new Update($this->table);
            $update->set([
                'start' => $start,
                'end' => $end,
                'modificadopor' => $registradopor,
                'fechahoramod' => date('Y-m-d H:i:s'),
            ]);
            $update->where("calendario_academico.idCalendarioAcademico =  $idCalendarioAcademico");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    //------------------------------------------------------------------------------
}
