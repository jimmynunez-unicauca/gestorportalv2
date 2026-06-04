<?php

namespace Administracion\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Administracion\Modelo\Entidades\Normatividad;
use Administracion\Modelo\Entidades\Proceso;

class NormatividadDAO extends AbstractTableGateway
{

    protected $table = 'normatividad';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'normatividad';
        $select = new Select($this->table);
        $select->columns([
            'idNormatividad',
            'nombre',
            'descripcion',
            'archivo',
            'tipo',
            'publicacion',
            'estado',
            'registradopor',
            'modificadopor',
            'fechahorareg',
            'fechahoramod',
        ]);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("normatividad.idNormatividad DESC")->limit(1000);
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getNormatividadDetalle($idNormatividad = 0)
    {
        $select = new Select('normatividad');
        $select->columns(array(
            'idNormatividad',
            'nombre',
            'descripcion',
            'archivo',
            'tipo',
            'publicacion',
            'estado',
            'registradopor',
            'modificadopor',
            'fechahorareg',
            'fechahoramod',
        ))->where("normatividad.idNormatividad = $idNormatividad")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getNormatividad($idNormatividad = 0)
    {
        return new Normatividad($this->select(array('idNormatividad' => $idNormatividad))->current()->getArrayCopy());
    }
    //------------------------------------------------------------------------------

    public function registrar(Normatividad $lumenOBJ = null)
    {
        try {
            $this->table = 'normatividad';
            $insert = new Insert($this->table);
            $datos = $lumenOBJ->getArrayCopy();
            unset($datos['idNormatividad']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function editar(Normatividad $lumenOBJ = null)
    {
        try {
            $this->table = 'normatividad';
            $idNormatividad = (int) $lumenOBJ->getIdNormatividad();
            $update = new Update($this->table);
            $datos = $lumenOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("normatividad.idNormatividad =  $idNormatividad");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function eliminar(Normatividad $lumenOBJ = null)
    {
        try {
            $this->table = "normatividad";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Eliminado',
                'modificadopor' => $lumenOBJ->getModificadopor(),
                'fechahoramod' => $lumenOBJ->getFechahoramod(),
            ]);
            $update->where("normatividad.idNormatividad = " . $lumenOBJ->getIdNormatividad());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function activar(Normatividad $archivoOBJ = null)
    {
        try {
            $this->table = "normatividad";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Activo',
                'modificadopor' => $archivoOBJ->getModificadopor(),
                'fechahoramod' => $archivoOBJ->getFechahoramod(),
            ]);
            $update->where("normatividad.idNormatividad = " . $archivoOBJ->getIdNormatividad());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    //------------------------------------------------------------------------------
}
