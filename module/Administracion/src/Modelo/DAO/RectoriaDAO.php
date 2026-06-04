<?php

namespace Administracion\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Administracion\Modelo\Entidades\Rectoria;
use Administracion\Modelo\Entidades\Proceso;

class RectoriaDAO extends AbstractTableGateway
{

    protected $table = 'rectoria';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'rectoria';
        $select = new Select($this->table);
        $select->columns(['*']);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("rectoria.idRectoria DESC")->limit(1000);
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getRectoriaDetalle($idRectoria = 0)
    {
        $select = new Select('rectoria');
        $select->columns(['*'])->where("rectoria.idRectoria = $idRectoria")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getRectoria($idRectoria = 0)
    {
        return new Rectoria($this->select(array('idRectoria' => $idRectoria))->current()->getArrayCopy());
    }
    //------------------------------------------------------------------------------

    public function registrar(Rectoria $lumenOBJ = null)
    {
        try {
            $this->table = 'rectoria';
            $insert = new Insert($this->table);
            $datos = $lumenOBJ->getArrayCopy();
            unset($datos['idRectoria']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function editar(Rectoria $lumenOBJ = null)
    {
        try {
            $this->table = 'rectoria';
            $idRectoria = (int) $lumenOBJ->getIdRectoria();
            $update = new Update($this->table);
            $datos = $lumenOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("rectoria.idRectoria =  $idRectoria");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function eliminar(Rectoria $lumenOBJ = null)
    {
        try {
            $this->table = "rectoria";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Eliminado',
                'modificadopor' => $lumenOBJ->getModificadopor(),
                'fechahoramod' => $lumenOBJ->getFechahoramod(),
            ]);
            $update->where("rectoria.idRectoria = " . $lumenOBJ->getIdRectoria());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function activar(Rectoria $archivoOBJ = null)
    {
        try {
            $this->table = "rectoria";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Activo',
                'modificadopor' => $archivoOBJ->getModificadopor(),
                'fechahoramod' => $archivoOBJ->getFechahoramod(),
            ]);
            $update->where("rectoria.idRectoria = " . $archivoOBJ->getIdRectoria());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    //------------------------------------------------------------------------------
    public function existeArchivo($archivo = '')
    {
        $this->table = 'rectoria';
        $rowset = $this->select(array('archivo' => $archivo));
        $row = $rowset->current();
        if (!$row) {
            return 0;
        }
        return 1;
    }
    //------------------------------------------------------------------------------
}
