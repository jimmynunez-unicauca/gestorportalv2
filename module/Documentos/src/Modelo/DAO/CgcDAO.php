<?php

namespace Documentos\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Documentos\Modelo\Entidades\Cgc;

class CgcDAO extends AbstractTableGateway
{

    protected $table = 'cgc';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'cgc';
        $select = new Select($this->table);
        $select->columns(['*']);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("cgc.idCGC DESC")->limit(1000);
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getCgcDetalle($idCGC = 0)
    {
        $select = new Select('cgc');
        $select->columns(['*'])->where("cgc.idCGC = $idCGC")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getCgc($idCGC = 0)
    {
        return new Cgc($this->select(array('idCGC' => $idCGC))->current()->getArrayCopy());
    }
    //------------------------------------------------------------------------------

    public function registrar(Cgc $lumenOBJ = null)
    {
        try {
            $this->table = 'cgc';
            $insert = new Insert($this->table);
            $datos = $lumenOBJ->getArrayCopy();
            unset($datos['idCGC']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function editar(Cgc $lumenOBJ = null)
    {
        try {
            $this->table = 'cgc';
            $idCGC = (int) $lumenOBJ->getIdCgc();
            $update = new Update($this->table);
            $datos = $lumenOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("cgc.idCGC =  $idCGC");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function eliminar(Cgc $lumenOBJ = null)
    {
        try {
            $this->table = "cgc";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Eliminado',
                'modificadopor' => $lumenOBJ->getModificadopor(),
                'fechahoramod' => $lumenOBJ->getFechahoramod(),
            ]);
            $update->where("cgc.idCGC = " . $lumenOBJ->getIdCgc());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function activar(Cgc $archivoOBJ = null)
    {
        try {
            $this->table = "cgc";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Activo',
                'modificadopor' => $archivoOBJ->getModificadopor(),
                'fechahoramod' => $archivoOBJ->getFechahoramod(),
            ]);
            $update->where("cgc.idCGC = " . $archivoOBJ->getIdCgc());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    //------------------------------------------------------------------------------
    public function existeArchivo($archivo = '')
    {
        $this->table = 'cgc';
        $rowset = $this->select(array('archivo' => $archivo));
        $row = $rowset->current();
        if (!$row) {
            return 0;
        }
        return 1;
    }
    //------------------------------------------------------------------------------
}
