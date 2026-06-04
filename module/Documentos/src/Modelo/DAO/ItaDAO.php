<?php

namespace Documentos\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Documentos\Modelo\Entidades\Ita;

class ItaDAO extends AbstractTableGateway
{

    protected $table = 'ita';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'ita';
        $select = new Select($this->table);
        $select->columns(['*']);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("ita.idITA DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getItaDetalle($idITA = 0)
    {
        $select = new Select('ita');
        $select->columns(['*'])->where("ita.idITA = $idITA")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getIta($idITA = 0)
    {
        return new Ita($this->select(array('idITA' => $idITA))->current()->getArrayCopy());
    }
    //------------------------------------------------------------------------------

    public function registrar(Ita $lumenOBJ = null)
    {
        try {
            $this->table = 'ita';
            $insert = new Insert($this->table);
            $datos = $lumenOBJ->getArrayCopy();
            unset($datos['idITA']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function editar(Ita $lumenOBJ = null)
    {
        try {
            $this->table = 'ita';
            $idITA = (int) $lumenOBJ->getIdIta();
            $update = new Update($this->table);
            $datos = $lumenOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("ita.idITA =  $idITA");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function eliminar(Ita $lumenOBJ = null)
    {
        try {
            $this->table = "ita";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Eliminado',
                'modificadopor' => $lumenOBJ->getModificadopor(),
                'fechahoramod' => $lumenOBJ->getFechahoramod(),
            ]);
            $update->where("ita.idITA = " . $lumenOBJ->getIdIta());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function activar(Ita $archivoOBJ = null)
    {
        try {
            $this->table = "ita";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Activo',
                'modificadopor' => $archivoOBJ->getModificadopor(),
                'fechahoramod' => $archivoOBJ->getFechahoramod(),
            ]);
            $update->where("ita.idITA = " . $archivoOBJ->getIdIta());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    //------------------------------------------------------------------------------
    public function existeArchivo($archivo = '')
    {
        $this->table = 'ita';
        $rowset = $this->select(array('archivo' => $archivo));
        $row = $rowset->current();
        if (!$row) {
            return 0;
        }
        return 1;
    }
    //------------------------------------------------------------------------------
    public function getRoles($idUsuario)
    {
        $this->table = 'usuario_rol';
        $select = new Select($this->table);
        $select->columns(['*'])
            ->join('roles', 'roles.idRol = usuario_rol.idRol', [
                'rol'
            ])
            ->where("usuario_rol.idUsuario = $idUsuario")->limit(1);
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    //------------------------------------------------------------------------------
}
