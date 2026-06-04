<?php

namespace Administracion\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Administracion\Modelo\Entidades\Directorio;
use Administracion\Modelo\Entidades\Proceso;

class DirectorioDAO extends AbstractTableGateway
{

    protected $table = 'directorioInstitucional';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'directorioInstitucional';
        $select = new Select($this->table);
        $select->columns([
            '*',
            'dependencia' => new Expression("(SELECT dependencia FROM dependencias WHERE directorioInstitucional.idDependencia = dependencias.idDependencia)"),
        ]);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("directorioInstitucional.idDI DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getDirectorioDetalle($idDI = 0)
    {
        $select = new Select('directorioInstitucional');
        $select->columns(['*'])->where("directorioInstitucional.idDI = $idDI")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getDirectorio($idDI = 0)
    {
        return new Directorio($this->select(array('idDI' => $idDI))->current()->getArrayCopy());
    }
    //------------------------------------------------------------------------------

    public function registrar(Directorio $dirOBJ = null)
    {
        try {
            $this->table = 'directorioInstitucional';
            $insert = new Insert($this->table);
            $datos = $dirOBJ->getArrayCopy();
            unset($datos['idDI']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function editar(Directorio $dirOBJ = null)
    {
        try {
            $this->table = 'directorioInstitucional';
            $idDI = (int) $dirOBJ->getIdDI();
            $update = new Update($this->table);
            $datos = $dirOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("directorioInstitucional.idDI =  $idDI");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function eliminar(Directorio $dirOBJ = null)
    {
        try {
            $this->table = "directorioInstitucional";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Eliminado',
                'modificadopor' => $dirOBJ->getModificadopor(),
                'fechahoramod' => $dirOBJ->getFechahoramod(),
            ]);
            $update->where("directorioInstitucional.idDI = " . $dirOBJ->getIdDI());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function activar(Directorio $archivoOBJ = null)
    {
        try {
            $this->table = "directorioInstitucional";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Activo',
                'modificadopor' => $archivoOBJ->getModificadopor(),
                'fechahoramod' => $archivoOBJ->getFechahoramod(),
            ]);
            $update->where("directorioInstitucional.idDI = " . $archivoOBJ->getIdDI());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
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
}
