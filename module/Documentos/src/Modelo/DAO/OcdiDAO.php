<?php

namespace Documentos\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Documentos\Modelo\Entidades\Ocdi;
use Documentos\Modelo\Entidades\Ocdidocumentos;

class OcdiDAO extends AbstractTableGateway
{

    protected $table = 'revistas_ocdi';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'revistas_ocdi';
        $select = new Select($this->table);
        $select->columns(['*']);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("revistas_ocdi.idRevistasOcdi DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getOcdiDetalle($idRevistasOcdi = 0)
    {
        $select = new Select('revistas_ocdi');
        $select->columns(['*'])
            ->where("revistas_ocdi.idRevistasOcdi = $idRevistasOcdi")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getOcdi($idRevistasOcdi = 0)
    {
        return new Ocdi($this->select(array('idRevistasOcdi' => $idRevistasOcdi))->current()->getArrayCopy());
    }
    //------------------------------------------------------------------------------

    public function registrar(Ocdi $ocdiOBJ = null)
    {
        try {
            $this->table = 'revistas_ocdi';
            $insert = new Insert($this->table);
            $datos = $ocdiOBJ->getArrayCopy();
            unset($datos['idRevistasOcdi']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function editar(Ocdi $ocdiOBJ = null)
    {
        try {
            $this->table = 'revistas_ocdi';
            $idRevistasOcdi = (int) $ocdiOBJ->getIdRevistasOcdi();
            $update = new Update($this->table);
            $datos = $ocdiOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("revistas_ocdi.idRevistasOcdi =  $idRevistasOcdi");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    //------------------------------------------------------------------------------
}
