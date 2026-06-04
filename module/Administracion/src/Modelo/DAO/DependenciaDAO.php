<?php

namespace Administracion\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Administracion\Modelo\Entidades\Dependencia;
use Administracion\Modelo\Entidades\Proceso;

class DependenciaDAO extends AbstractTableGateway
{

    protected $table = 'dependencias';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'dependencias';
        $select = new Select($this->table);
        $select->columns(['*']);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("dependencias.idDependencia DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getDependenciaDetalle($idDependencia = 0)
    {
        $select = new Select('dependencias');
        $select->columns(['*'])->where("dependencias.idDependencia = $idDependencia")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getDependencia($idDependencia = 0)
    {
        return new Dependencia($this->select(array('idDependencia' => $idDependencia))->current()->getArrayCopy());
    }
    //------------------------------------------------------------------------------

    public function registrar(Dependencia $lumenOBJ = null)
    {
        try {
            $this->table = 'dependencias';
            $insert = new Insert($this->table);
            $datos = $lumenOBJ->getArrayCopy();
            unset($datos['idDependencia']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function editar(Dependencia $lumenOBJ = null)
    {
        try {
            $this->table = 'dependencias';
            $idDependencia = (int) $lumenOBJ->getIdDependencia();
            $update = new Update($this->table);
            $datos = $lumenOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("dependencias.idDependencia =  $idDependencia");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function eliminar(Dependencia $lumenOBJ = null)
    {
        try {
            $this->table = "dependencias";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Eliminado',
                'modificadopor' => $lumenOBJ->getModificadopor(),
                'fechahoramod' => $lumenOBJ->getFechahoramod(),
            ]);
            $update->where("dependencias.idDependencia = " . $lumenOBJ->getIdDependencia());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function activar(Dependencia $archivoOBJ = null)
    {
        try {
            $this->table = "dependencias";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Activo',
                'modificadopor' => $archivoOBJ->getModificadopor(),
                'fechahoramod' => $archivoOBJ->getFechahoramod(),
            ]);
            $update->where("dependencias.idDependencia = " . $archivoOBJ->getIdDependencia());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    //------------------------------------------------------------------------------
    public function getProcesos()
    {
        $this->table = 'proceso';
        $select = new Select($this->table);
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getTipoProceso($idProceso = 0)
    {
        $select = new Select('tipo_proceso');
        $select->columns(array(
            'idTipoProceso',
            'idProceso',
            'tipoProceso'
        ))->where("tipo_proceso.idProceso = $idProceso");
        return $this->selectWith($select)->toArray();
    }
    public function getSubproceso($idTipoProceso = 0)
    {
        $select = new Select('subproceso');
        $select->columns(array(
            'idSubproceso',
            'idTipoProceso',
            'subproceso'
        ))->where("subproceso.idTipoProceso = $idTipoProceso");
        return $this->selectWith($select)->toArray();
    }
    public function getSubProcesoOBJ($idSubproceso = 0)
    {
        $this->table = 'subproceso';
        $select = new Select($this->table);
        $select->columns(array(
            'idSubproceso',
            'idTipoProceso',
            'subproceso'
        ))->join('tipo_proceso', 'tipo_proceso.idTipoProceso = subproceso.idTipoProceso', array(
            'idProceso',
            'tipoProceso'
        ))->join('proceso', 'proceso.idProceso = tipo_proceso.idProceso', array(
            'proceso'
        ));
        $select->where('subproceso.idSubproceso = ' . $idSubproceso);
        //        print $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        foreach ($datos as $dato) {
            return new Proceso($dato);
        }
        return null;
    }
    //------------------------------------------------------------------------------
    public function getDependencias()
    {
        $this->table = 'dependencias';
        $select = new Select($this->table);
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    //------------------------------------------------------------------------------
    public function existeArchivo($archivo = '')
    {
        $this->table = 'dependencias';
        $rowset = $this->select(array('archivo' => $archivo));
        $row = $rowset->current();
        if (!$row) {
            return 0;
        }
        return 1;
    }
    //------------------------------------------------------------------------------
}
