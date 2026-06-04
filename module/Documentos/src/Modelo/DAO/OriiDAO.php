<?php

namespace Documentos\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Documentos\Modelo\Entidades\Orii;
use Documentos\Modelo\Entidades\Oriidocumentos;

class OriiDAO extends AbstractTableGateway
{

    protected $table = 'orii';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'orii';
        $select = new Select($this->table);
        $select->columns(['*'])
            ->join('instituto', 'instituto.idInstituto = orii.idInstituto', ['*'])
            ->join('ciudades', 'ciudades.idCiudades = instituto.idCiudades', ['*'])
            ->join('countries', 'countries.id = ciudades.idCountries', ['*']);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("orii.idOrii DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getOriiDetalle($idOrii = 0)
    {
        $select = new Select('orii');
        $select->columns(['*'])
            ->join('instituto', 'instituto.idInstituto = orii.idInstituto', ['*'])
            ->where("orii.idOrii = $idOrii")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getOrii($idOrii = 0)
    {
        return new Orii($this->select(array('idOrii' => $idOrii))->current()->getArrayCopy());
    }
    //------------------------------------------------------------------------------

    public function registrar(Orii $oriiOBJ = null)
    {
        try {
            $this->table = 'orii';
            $insert = new Insert($this->table);
            $datos = $oriiOBJ->getArrayCopy();
            unset($datos['idOrii']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function registrarDocumento(Oriidocumentos $oriiOBJ = null)
    {
        try {
            $this->table = 'documentos_orii';
            $insert = new Insert($this->table);
            $datos = $oriiOBJ->getArrayCopy();
            unset($datos['id_documentos_orii']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function editar(Orii $oriiOBJ = null)
    {
        try {
            $this->table = 'orii';
            $idOrii = (int) $oriiOBJ->getIdOrii();
            $update = new Update($this->table);
            $datos = $oriiOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("orii.idOrii =  $idOrii");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function eliminarDoc($id_documentos_orii = 0)
    {
        try {
            $this->table = 'documentos_orii';
            $delete = new Delete($this->table);
            $delete->where("id_documentos_orii = $id_documentos_orii");
            //            echo $delete->getSqlString();
            return $this->deleteWith($delete);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    //------------------------------------------------------------------------------
    public function getInstituciones()
    {
        $this->table = 'instituto';
        $select = new Select($this->table);
        $select->columns(['*'])
            ->join('ciudades', 'ciudades.idCiudades = instituto.idCiudades', ['*'])
            ->join('countries', 'countries.id = ciudades.idCountries', ['*']);
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    //------------------------------------------------------------------------------
    public function getOriiDocumentos($id)
    {
        $this->table = 'documentos_orii';
        $select = new Select($this->table);
        $select->columns(['*'])
            ->where("documentos_orii.idOrii =  $id");
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    //------------------------------------------------------------------------------
}
