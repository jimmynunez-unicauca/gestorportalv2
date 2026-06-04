<?php

namespace Documentos\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Documentos\Modelo\Entidades\Archivohistorico;

class ArchivohistoricoDAO extends AbstractTableGateway
{

    protected $table = 'archivo_historico';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'archivo_historico';
        $select = new Select($this->table);
        $select->columns(['*',]);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("archivo_historico.idarchivo_historico DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }

    public function getArchivoDetalle($id = 0)
    {
        $select = new Select('archivo_historico');
        $select->columns(['*'])->where("archivo_historico.idarchivo_historico = $id")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }

    public function getArchivohistorico($id = 0)
    {
        return new Archivohistorico($this->select(array('idarchivo_historico' => $id))->current()->getArrayCopy());
    }

    //------------------------------------------------------------------------------
    public function registrar(Archivohistorico $sgOBJ = null)
    {
        try {
            $this->table = 'archivo_historico';
            $insert = new Insert($this->table);
            $datos = $sgOBJ->getArrayCopy();
            unset($datos['idarchivo_historico']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    public function editar(Archivohistorico $sgOBJ = null)
    {
        try {
            $this->table = 'archivo_historico';
            $id = (int) $sgOBJ->getIdarchivo_historico();
            $update = new Update($this->table);
            $datos = $sgOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("archivo_historico.idarchivo_historico = $id");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    public function eliminar(Archivohistorico $sgOBJ = null)
    {
        try {
            $this->table = "archivo_historico";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Eliminado',
                'modificadopor' => $sgOBJ->getModificadopor(),
                'fechahoramod' => $sgOBJ->getFechahoramod(),
            ]);
            $update->where("archivo_historico.idarchivo_historico = " . $sgOBJ->getIdarchivo_historico());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    public function activar(Archivohistorico $sgOBJ = null)
    {
        try {
            $this->table = "archivo_historico";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Activo',
                'modificadopor' => $sgOBJ->getModificadopor(),
                'fechahoramod' => $sgOBJ->getFechahoramod(),
            ]);
            $update->where("archivo_historico.idarchivo_historico = " . $sgOBJ->getIdarchivo_historico());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    //------------------------------------------------------------------------------
}
