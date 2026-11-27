<?php

namespace Administracion\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Administracion\Modelo\Entidades\Programaacademico;

class ProgramaacademicoDAO extends AbstractTableGateway
{
    protected $table = 'programas';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function fetchAll($filtro = '')
    {
        $select = new Select($this->table);
        $select->columns(['*'])
            ->join(
                ['f' => 'facultades'],
                'programas.idFacultad = f.idFacultad',
                ['facultad' => 'facultad'],
                Select::JOIN_LEFT
            );
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order('programas.programa ASC');
        }
        return $this->selectWith($select)->toArray();
    }

    public function getPrograma($idPrograma = 0)
    {
        $select = new Select($this->table);
        $select->where(['idPrograma' => (int) $idPrograma])->limit(1);
        $row = $this->selectWith($select)->current();
        return $row ? new Programaacademico($row->getArrayCopy()) : null;
    }

    public function getProgramaDetalle($idPrograma = 0)
    {
        $select = new Select($this->table);
        $select->columns(['*'])
            ->join(
                ['f' => 'facultades'],
                'programas.idFacultad = f.idFacultad',
                ['facultad' => 'facultad'],
                Select::JOIN_LEFT
            )
            ->where(['programas.idPrograma' => (int) $idPrograma])
            ->limit(1);
        $datos = $this->selectWith($select)->toArray();
        return count($datos) > 0 ? $datos[0] : null;
    }

    public function registrar(Programaacademico $obj)
    {
        $insert = new Insert($this->table);
        $insert->values([
            'idFacultad' => (int) $obj->getIdFacultad(),
            'programa'   => $obj->getPrograma(),
        ]);
        $this->insertWith($insert);
    }

    public function editar(Programaacademico $obj)
    {
        $update = new Update($this->table);
        $update->set([
            'idFacultad' => (int) $obj->getIdFacultad(),
            'programa'   => $obj->getPrograma(),
        ]);
        $update->where(['idPrograma' => (int) $obj->getIdPrograma()]);
        $this->updateWith($update);
    }

    public function borrarPrograma($idPrograma)
    {
        $delete = new Delete($this->table);
        $delete->where(['idPrograma' => (int) $idPrograma]);
        $this->deleteWith($delete);
    }
}
