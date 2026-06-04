<?php

namespace Administracion\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Administracion\Modelo\Entidades\Contribucionesacademicas;

class ContribucionesacademicasDAO extends AbstractTableGateway
{

    protected $table = 'contrubuciones_academicas';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'contrubuciones_academicas';
        $select = new Select($this->table);
        $select->columns([
            '*',
            'programa' => new Expression("(SELECT programa FROM programas WHERE contrubuciones_academicas.idPrograma = programas.idPrograma)"),
            'idF' => new Expression("(SELECT idFacultad FROM programas WHERE contrubuciones_academicas.idPrograma = programas.idPrograma)"),
            'facultad' => new Expression("(SELECT facultad FROM facultades WHERE facultades.idFacultad = idF)"),
        ]);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("contrubuciones_academicas.idCA DESC")->limit(1000);
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getContribucionesacademicasDetalle($id = 0)
    {
        $select = new Select('contrubuciones_academicas');
        $select->columns([
            '*',
            'programa' => new Expression("(SELECT programa FROM programas WHERE contrubuciones_academicas.idPrograma = programas.idPrograma)"),
            'idF' => new Expression("(SELECT idFacultad FROM programas WHERE contrubuciones_academicas.idPrograma = programas.idPrograma)"),
            'facultad' => new Expression("(SELECT facultad FROM facultades WHERE facultades.idFacultad = idF)"),
        ])->where("contrubuciones_academicas.idCA = $id")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getContribucionesacademicas($id = 0)
    {
        return new Contribucionesacademicas($this->select(array('idCA' => $id))->current()->getArrayCopy());
    }
    //------------------------------------------------------------------------------

    public function registrar(Contribucionesacademicas $caOBJ = null)
    {
        try {
            $this->table = 'contrubuciones_academicas';
            $insert = new Insert($this->table);
            $datos = $caOBJ->getArrayCopy();
            unset($datos['idCA']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function editar(Contribucionesacademicas $caOBJ = null)
    {
        try {
            $this->table = 'contrubuciones_academicas';
            $id = (int) $caOBJ->getidCA();
            $update = new Update($this->table);
            $datos = $caOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("contrubuciones_academicas.idCA =  $id");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function eliminar(Contribucionesacademicas $caOBJ = null)
    {
        try {
            $this->table = "contrubuciones_academicas";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Eliminado',
                'modificadopor' => $caOBJ->getModificadopor(),
                'fechahoramod' => $caOBJ->getFechahoramod(),
            ]);
            $update->where("contrubuciones_academicas.idCA = " . $caOBJ->getidCA());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function activar(Contribucionesacademicas $archivoOBJ = null)
    {
        try {
            $this->table = "contrubuciones_academicas";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Activo',
                'modificadopor' => $archivoOBJ->getModificadopor(),
                'fechahoramod' => $archivoOBJ->getFechahoramod(),
            ]);
            $update->where("contrubuciones_academicas.idCA = " . $archivoOBJ->getidCA());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    //------------------------------------------------------------------------------
    public function getFacultades()
    {
        $this->table = 'facultades';
        $select = new Select($this->table);
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getProgramas($idFacultad = 0)
    {
        $select = new Select('programas');
        $select->columns(['*'])->where("programas.idFacultad = $idFacultad");
        return $this->selectWith($select)->toArray();
    }
    //------------------------------------------------------------------------------
    public function getProgramasPorFacultad($idFacultad)
    {
        $select = new Select('programas');
        $select->columns(['*']);
        $select->where->equalTo('idFacultad', $idFacultad);
        $resultSet = $this->selectWith($select);
        $programas = [];
        foreach ($resultSet as $row) {
            $programas[] = [
                'idPrograma' => $row->idPrograma,
                'programa' => $row->programa
            ];
        }

        return $programas;
    }
    //------------------------------------------------------------------------------
    public function existeArchivo($archivo = '')
    {
        $this->table = 'contrubuciones_academicas';
        $rowset = $this->select(array('archivo' => $archivo));
        $row = $rowset->current();
        if (!$row) {
            return 0;
        }
        return 1;
    }
    //------------------------------------------------------------------------------
}
