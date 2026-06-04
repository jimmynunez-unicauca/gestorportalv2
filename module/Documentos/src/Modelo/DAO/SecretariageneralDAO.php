<?php

namespace Documentos\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Documentos\Modelo\Entidades\Secretariageneral;

class SecretariageneralDAO extends AbstractTableGateway
{

    protected $table = 'doc_secretariageneral';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'doc_secretariageneral';
        $select = new Select($this->table);
        $select->columns(['*',]);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("doc_secretariageneral.id DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }

    public function getArchivoDetalle($id = 0)
    {
        $select = new Select('doc_secretariageneral');
        $select->columns(['*'])->where("doc_secretariageneral.id = $id")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }

    public function getSecretariageneral($id = 0)
    {
        return new Secretariageneral($this->select(array('id' => $id))->current()->getArrayCopy());
    }

    //------------------------------------------------------------------------------
    public function registrar(Secretariageneral $sgOBJ = null)
    {
        try {
            $this->table = 'doc_secretariageneral';
            $insert = new Insert($this->table);
            $datos = $sgOBJ->getArrayCopy();
            unset($datos['id']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    public function editar(Secretariageneral $sgOBJ = null)
    {
        try {
            $this->table = 'doc_secretariageneral';
            $id = (int) $sgOBJ->getid();
            $update = new Update($this->table);
            $datos = $sgOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("doc_secretariageneral.id = $id");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    public function eliminar(Secretariageneral $sgOBJ = null)
    {
        try {
            $this->table = "doc_secretariageneral";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Eliminado',
                'modificadopor' => $sgOBJ->getModificadopor(),
                'fechahoramod' => $sgOBJ->getFechahoramod(),
            ]);
            $update->where("doc_secretariageneral.id = " . $sgOBJ->getid());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    public function activar(Secretariageneral $sgOBJ = null)
    {
        try {
            $this->table = "doc_secretariageneral";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Activo',
                'modificadopor' => $sgOBJ->getModificadopor(),
                'fechahoramod' => $sgOBJ->getFechahoramod(),
            ]);
            $update->where("doc_secretariageneral.id = " . $sgOBJ->getid());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    //------------------------------------------------------------------------------
}
