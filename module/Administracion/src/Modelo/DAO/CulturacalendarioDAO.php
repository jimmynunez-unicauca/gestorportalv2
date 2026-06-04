<?php

namespace Administracion\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Administracion\Modelo\Entidades\Culturacalendario;

class CulturacalendarioDAO extends AbstractTableGateway
{

    protected $table = 'calendario_cultura';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'calendario_cultura';
        $select = new Select($this->table);
        $select->columns([
            'idCalendarioCultura',
            'tipo',
            'title',
            'descripcion',
            'dirigido',
            'costo',
            'organizado',
            'imagen',
            'lugar',
            'start',
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
            $select->order("calendario_cultura.idCalendarioCultura DESC")->limit(25);
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getCulturaDetalle($idCalendarioCultura = 0)
    {
        $select = new Select('calendario_cultura');
        $select->columns(array(
            'idCalendarioCultura',
            'tipo',
            'title',
            'descripcion',
            'dirigido',
            'costo',
            'organizado',
            'imagen',
            'lugar',
            'start',
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
        ))->where("calendario_cultura.idCalendarioCultura = $idCalendarioCultura")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getCultura($idCalendarioCultura = 0)
    {
        return new Culturacalendario($this->select(array('idCalendarioCultura' => $idCalendarioCultura))->current()->getArrayCopy());
    }

    //------------------------------------------------------------------------------
    public function registrar(Culturacalendario $CalendarioOBJ = null)
    {
        try {
            $this->table = 'calendario_cultura';
            $insert = new Insert($this->table);
            $datos = $CalendarioOBJ->getArrayCopy();
            unset($datos['idCalendarioCultura']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function editar(Culturacalendario $CalendarioOBJ = null)
    {
        try {
            $this->table = 'calendario_cultura';
            $idCalendarioCultura = (int) $CalendarioOBJ->getIdCalendarioCultura();
            $update = new Update($this->table);
            $datos = $CalendarioOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("calendario_cultura.idCalendarioCultura =  $idCalendarioCultura");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function eliminar($idCalendarioCultura = 0, $registradopor = '')
    {
        try {
            $this->table = "calendario_cultura";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Eliminado',
                'modificadopor' => $registradopor,
                'fechahoramod' => date('Y-m-d H:i:s'),
            ]);
            $update->where("calendario_cultura.idCalendarioCultura = $idCalendarioCultura");
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    //------------------------------------------------------------------------------
    public function moverevento($idCalendarioCultura = 0, $start = '', $end = '', $registradopor = '')
    {
        try {
            $this->table = 'calendario_cultura';
            $update = new Update($this->table);
            $update->set([
                'start' => $start,
                'end' => $end,
                'modificadopor' => $registradopor,
                'fechahoramod' => date('Y-m-d H:i:s'),
            ]);
            $update->where("calendario_cultura.idCalendarioCultura =  $idCalendarioCultura");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    //------------------------------------------------------------------------------
}
