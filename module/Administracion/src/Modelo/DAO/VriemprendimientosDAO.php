<?php

namespace Administracion\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Administracion\Modelo\Entidades\Vriemprendimientos;

class VriemprendimientosDAO extends AbstractTableGateway
{

    protected $table = 'vri_emprendimientos';

    //------------------------------------------------------------------------------
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }
    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'vri_emprendimientos';
        $select = new Select($this->table);
        $select->columns(['*',]);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("vri_emprendimientos.id DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    //------------------------------------------------------------------------------
    public function getVriemprendimientosDetalle($id = 0)
    {
        $select = new Select('vri_emprendimientos');
        $select->columns(['*'])->where("vri_emprendimientos.id = $id")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getVriemprendimientos($id = 0)
    {
        return new Vriemprendimientos($this->select(array('id' => $id))->current()->getArrayCopy());
    }
    //------------------------------------------------------------------------------
    public function registrar(Vriemprendimientos $vriemprendimientosOBJ = null)
    {
        try {
            $this->table = 'vri_emprendimientos';
            $insert = new Insert($this->table);
            $datos = $vriemprendimientosOBJ->getArrayCopy();
            unset($datos['id']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function editar(Vriemprendimientos $vriemprendimientosOBJ = null)
    {
        try {
            $this->table = 'vri_emprendimientos';
            $id = (int) $vriemprendimientosOBJ->getId();
            $update = new Update($this->table);
            $datos = $vriemprendimientosOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("vri_emprendimientos.id =  $id");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    //------------------------------------------------------------------------------
    public function eliminar(Vriemprendimientos $vriemprendimientosOBJ = null)
    {
        return $this->delete(['id' => (int) $vriemprendimientosOBJ->getId()]);
    }

    public function cambiarEstado($id, $estado)
    {
        $data = [
            'estado' => $estado,
            'modificadopor' => new Expression('NOW()'),
        ];

        $update = new Update($this->table);
        $update->set($data);
        $update->where(['id' => (int) $id]);
        return $this->updateWith($update);
    }
    //------------------------------------------------------------------------------
}
