<?php

namespace Administracion\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Administracion\Modelo\Entidades\Cpnormativa;

class CpnormativaDAO extends AbstractTableGateway
{

    protected $table = 'cp_normativa';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'cp_normativa';
        $select = new Select($this->table);
        $select->columns([
            'idCPNormativa',
            'idEmitido',
            'nombre',
            'descripcion',
            'dirigido',
            'archivo',
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
            $select->order("cp_normativa.idCPNormativa DESC")->limit(1000);
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getCpnormativaDetalle($idCPNormativa = 0)
    {
        $select = new Select('cp_normativa');
        $select->columns(array(
            'idCPNormativa',
            'idEmitido',
            'nombre',
            'descripcion',
            'dirigido',
            'archivo',
            'publicacion',
            'estado',
            'registradopor',
            'modificadopor',
            'fechahorareg',
            'fechahoramod',
        ))->where("cp_normativa.idCPNormativa = $idCPNormativa")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getCpnormativa($idCPNormativa = 0)
    {
        return new Cpnormativa($this->select(array('idCPNormativa' => $idCPNormativa))->current()->getArrayCopy());
    }
    //------------------------------------------------------------------------------

    public function registrar(Cpnormativa $lumenOBJ = null)
    {
        try {
            $this->table = 'cp_normativa';
            $insert = new Insert($this->table);
            $datos = $lumenOBJ->getArrayCopy();
            unset($datos['idCPNormativa']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function editar(Cpnormativa $lumenOBJ = null)
    {
        try {
            $this->table = 'cp_normativa';
            $idCPNormativa = (int) $lumenOBJ->getIdCpnormativa();
            $update = new Update($this->table);
            $datos = $lumenOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("cp_normativa.idCPNormativa =  $idCPNormativa");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function eliminar(Cpnormativa $lumenOBJ = null)
    {
        try {
            $this->table = "cp_normativa";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Eliminado',
                'modificadopor' => $lumenOBJ->getModificadopor(),
                'fechahoramod' => $lumenOBJ->getFechahoramod(),
            ]);
            $update->where("cp_normativa.idCPNormativa = " . $lumenOBJ->getIdCpnormativa());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function activar(Cpnormativa $archivoOBJ = null)
    {
        try {
            $this->table = "cp_normativa";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Activo',
                'modificadopor' => $archivoOBJ->getModificadopor(),
                'fechahoramod' => $archivoOBJ->getFechahoramod(),
            ]);
            $update->where("cp_normativa.idCPNormativa = " . $archivoOBJ->getIdCpnormativa());
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
    public function existeArchivo($archivo = '')
    {
        $this->table = 'cp_normativa';
        $rowset = $this->select(array('archivo' => $archivo));
        $row = $rowset->current();
        if (!$row) {
            return 0;
        }
        return 1;
    }
    //------------------------------------------------------------------------------
}
