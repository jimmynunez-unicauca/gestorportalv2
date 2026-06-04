<?php

namespace Administracion\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Administracion\Modelo\Entidades\Evento;

class EventoDAO extends AbstractTableGateway
{

    protected $table = 'evento';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'evento';
        $select = new Select($this->table);
        $select->columns(['*']);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("evento.idEvento DESC")->limit(25);
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getEventoDetalle($idEvento = 0)
    {
        $select = new Select('evento');
        $select->columns(['*'])->where("evento.idEvento = $idEvento")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getEvento($idEvento = 0)
    {
        return new Evento($this->select(array('idEvento' => $idEvento))->current()->getArrayCopy());
    }
    //------------------------------------------------------------------------------

    public function registrar(Evento $CalendarioOBJ = null)
    {
        try {
            $this->table = 'evento';
            $insert = new Insert($this->table);
            $datos = $CalendarioOBJ->getArrayCopy();
            unset($datos['idEvento']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function editar(Evento $CalendarioOBJ = null)
    {
        try {
            $this->table = 'evento';
            $idEvento = (int) $CalendarioOBJ->getIdEvento();
            $update = new Update($this->table);
            $datos = $CalendarioOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("evento.idEvento =  $idEvento");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function eliminar($idEvento = 0, $registradopor = '')
    {
        try {
            $this->table = "evento";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Eliminado',
                'modificadopor' => $registradopor,
                'fechahoramod' => date('Y-m-d H:i:s'),
            ]);
            $update->where("evento.idEvento = $idEvento");
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    //------------------------------------------------------------------------------
    public function moverevento($idEvento = 0, $start = '', $end = '', $registradopor = '')
    {
        try {
            $this->table = 'evento';
            $update = new Update($this->table);
            $update->set([
                'start' => $start,
                'end' => $end,
                'modificadopor' => $registradopor,
                'fechahoramod' => date('Y-m-d H:i:s'),
            ]);
            $update->where("evento.idEvento =  $idEvento");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    //------------------------------------------------------------------------------
}
