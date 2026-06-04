<?php

namespace Documentos\Modelo\DAO;

use Documentos\Formularios\UnisaludrendicioncuentasForm;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Documentos\Modelo\Entidades\Unisaludrendicioncuentas;

class UnisaludrendicioncuentasDAO extends AbstractTableGateway
{

    protected $table = 'unisalud_rendicion_cuentas';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'unisalud_rendicion_cuentas';
        $select = new Select($this->table);
        $select->columns(['*',]);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("unisalud_rendicion_cuentas.id DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }

    public function getArchivoDetalle($id = 0)
    {
        $select = new Select('unisalud_rendicion_cuentas');
        $select->columns(['*'])->where("unisalud_rendicion_cuentas.id = $id")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }

    public function getUnisaludrendicioncuentas($id = 0)
    {
        return new Unisaludrendicioncuentas($this->select(array('id' => $id))->current()->getArrayCopy());
    }

    //------------------------------------------------------------------------------
    public function registrar(Unisaludrendicioncuentas $sgOBJ = null)
    {
        try {
            $this->table = 'unisalud_rendicion_cuentas';
            $insert = new Insert($this->table);
            $datos = $sgOBJ->getArrayCopy();
            unset($datos['id']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    public function editar(Unisaludrendicioncuentas $sgOBJ = null)
    {
        try {
            $id = (int) $sgOBJ->getId();
            $update = new Update($this->table);
            $datos = $sgOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("unisalud_rendicion_cuentas.id =  $id");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    public function eliminar(Unisaludrendicioncuentas $sgOBJ = null)
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
