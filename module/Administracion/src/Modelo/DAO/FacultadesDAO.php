<?php

namespace Administracion\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Expression;
use Administracion\Modelo\Entidades\Facultad;

class FacultadesDAO extends AbstractTableGateway
{
    protected $table = 'facultades';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function fetchAll($filtro = '')
    {
        $select = new Select($this->table);
        $select->columns(['*']);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order('facultad ASC');
        }
        return $this->selectWith($select)->toArray();
    }

    public function getFacultad($idFacultad = 0)
    {
        $select = new Select($this->table);
        $select->where(['idFacultad' => (int) $idFacultad])->limit(1);
        $row = $this->selectWith($select)->current();
        return $row ? new Facultad($row->getArrayCopy()) : null;
    }

    public function getFacultadDetalle($idFacultad = 0)
    {
        $select = new Select($this->table);
        $select->where(['idFacultad' => (int) $idFacultad])->limit(1);
        $datos = $this->selectWith($select)->toArray();
        return count($datos) > 0 ? $datos[0] : null;
    }

    public function getFacultadesSelect()
    {
        $select = new Select($this->table);
        $select->columns(['idFacultad', 'facultad'])->order('facultad ASC');
        return $this->selectWith($select)->toArray();
    }

    public function registrar(Facultad $obj)
    {
        $insert = new Insert($this->table);
        $insert->values(['facultad' => $obj->getFacultad()]);
        $this->insertWith($insert);
    }

    public function editar(Facultad $obj)
    {
        $update = new Update($this->table);
        $update->set(['facultad' => $obj->getFacultad()]);
        $update->where(['idFacultad' => (int) $obj->getIdFacultad()]);
        $this->updateWith($update);
    }

    public function borrarFacultad($idFacultad)
    {
        $delete = new Delete($this->table);
        $delete->where(['idFacultad' => (int) $idFacultad]);
        $this->deleteWith($delete);
    }

    // Verifica si la facultad tiene programas académicos asociados
    public function tieneProgramas($idFacultad)
    {
        $select = new Select('programas');
        $select->columns(['total' => new Expression('COUNT(*)')])
            ->where(['idFacultad' => (int) $idFacultad]);
        $row = $this->selectWith($select)->current();
        return $row && (int) $row['total'] > 0;
    }
}
