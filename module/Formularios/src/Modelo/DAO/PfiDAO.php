<?php

namespace Formularios\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Formularios\Modelo\Entidades\Pfi;

class PfiDAO extends AbstractTableGateway
{

    protected $table = 'form_psi_config';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'form_psi_config';
        $select = new Select($this->table);
        $select->columns(['*']);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("form_psi_config.id_config  DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getFormDetalle($id = 0)
    {
        $select = new Select('form_psi_config');
        $select->columns(['*'])->where("form_psi_config.id_config = $id")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getFormPfi($idConfig = 0)
    {
        return new Pfi($this->select(array('id_config' => $idConfig))->current()->getArrayCopy());
    }
    //------------------------------------------------------------------------------
    public function registrar(Pfi $pfiOBJ = null)
    {
        try {
            $this->table = 'form_psi_config';
            $insert = new Insert($this->table);
            $datos = $pfiOBJ->getArrayCopy();
            unset($datos['id_config']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    //------------------------------------------------------------------------------
    public function editar(Pfi $pfiOBJ = null)
    {
        try {
            $this->table = 'form_psi_config';
            $idConfig = (int) $pfiOBJ->getIdConfig();
            $update = new Update($this->table);
            $datos = $pfiOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("form_psi_config.id_config =  $idConfig");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    //------------------------------------------------------------------------------
    public function eliminar(Pfi $pfiOBJ = null)
    {
        return $this->delete(['id_config' => (int) $pfiOBJ->getIdConfig()]);
    }

    public function cambiarEstado($idConfig, $estado)
    {
        $data = [
            'activo' => $estado,
            'updated_at' => new Expression('NOW()'),
        ];

        $update = new Update($this->table);
        $update->set($data);
        $update->where(['id_config' => (int) $idConfig]);
        return $this->updateWith($update);
    }
    //------------------------------------------------------------------------------

}
