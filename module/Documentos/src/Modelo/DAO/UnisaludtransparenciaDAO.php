<?php

namespace Documentos\Modelo\DAO;

use Documentos\Formularios\UnisaludtransparenciaForm;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Documentos\Modelo\Entidades\Unisaludtransparencia;

class UnisaludtransparenciaDAO extends AbstractTableGateway
{

    protected $table = 'unisalud_transparencia';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'unisalud_transparencia';
        $select = new Select($this->table);
        $select->columns(['*',]);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("unisalud_transparencia.id DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }

    public function getArchivoDetalle($id = 0)
    {
        $select = new Select('unisalud_transparencia');
        $select->columns(['*'])->where("unisalud_transparencia.id = $id")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }

    public function getUnisaludtransparencia($id = 0)
    {
        return new Unisaludtransparencia($this->select(array('id' => $id))->current()->getArrayCopy());
    }

    //------------------------------------------------------------------------------
    public function registrar(Unisaludtransparencia $sgOBJ = null)
    {
        try {
            $this->table = 'unisalud_transparencia';
            $insert = new Insert($this->table);
            $datos = $sgOBJ->getArrayCopy();
            unset($datos['id']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    public function editar(Unisaludtransparencia $sgOBJ = null)
    {
        try {
            $id = (int) $sgOBJ->getId();
            $update = new Update($this->table);
            $datos = $sgOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("unisalud_transparencia.id =  $id");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    public function eliminar(Unisaludtransparencia $sgOBJ = null)
    {
        return $this->delete(['id' => (int) $sgOBJ->getId()]);
    }

    public function cambiarEstado($id, $estado)
    {
        $data = [
            'activo' => $estado,
            'actualizado_el' => new Expression('NOW()'),
        ];

        $update = new Update($this->table);
        $update->set($data);
        $update->where(['id' => (int) $id]);
        return $this->updateWith($update);
    }
}
