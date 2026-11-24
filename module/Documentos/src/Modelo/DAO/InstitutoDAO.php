<?php

namespace Documentos\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Expression;
use Documentos\Modelo\Entidades\Instituto;

class InstitutoDAO extends AbstractTableGateway
{
    protected $table = 'instituto';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //==========================================================================
    public function fetchAll($filtro = '')
    {
        $select = new Select('instituto');
        $select->columns(['*'])
            ->join(
                ['c' => 'ciudades'],
                'instituto.idCiudades = c.idCiudades',
                [
                    'ciudad'          => 'ciudad',
                    'state_province'  => 'state_province',   // <-- NUEVO
                ],
                Select::JOIN_LEFT
            );
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order('instituto.idInstituto DESC');
        }
        return $this->selectWith($select)->toArray();
    }

    public function getInstituto($idInstituto = 0)
    {
        $select = new Select('instituto');
        $select->where(['idInstituto' => (int) $idInstituto])->limit(1);
        $row = $this->selectWith($select)->current();
        return $row ? new Instituto($row->getArrayCopy()) : null;
    }

    public function getInstitutoDetalle($idInstituto = 0)
    {
        $select = new Select('instituto');
        $select->columns(['*'])
            ->join(
                ['c' => 'ciudades'],
                'instituto.idCiudades = c.idCiudades',
                [
                    'ciudad'          => 'ciudad',
                    'state_province'  => 'state_province',   // <-- NUEVO
                ],
                Select::JOIN_LEFT
            )
            ->where(['instituto.idInstituto' => (int) $idInstituto])
            ->limit(1);

        $datos = $this->selectWith($select)->toArray();
        return count($datos) > 0 ? $datos[0] : null;
    }

    public function registrar(Instituto $obj)
    {
        $insert = new Insert('instituto');
        $datos = $obj->getArrayCopy();
        unset($datos['idInstituto']);
        $insert->values($datos);
        $this->insertWith($insert);
    }

    public function editar(Instituto $obj)
    {
        $id = (int) $obj->getIdInstituto();
        $update = new Update('instituto');
        $datos = $obj->getArrayCopy();
        unset($datos['idInstituto']);
        $update->set($datos);
        $update->where(['idInstituto' => $id]);
        $this->updateWith($update);
    }

    public function eliminarActivar(Instituto $obj, $estado = '')
    {
        $update = new Update('instituto');
        $update->set([
            'estado'        => $estado,
            'modificadopor' => $obj->getModificadopor(),
            'fechahoramod'  => $obj->getFechahoramod(),
        ]);
        $update->where(['idInstituto' => (int) $obj->getIdInstituto()]);
        $this->updateWith($update);
    }

    public function borrarInstituto($idInstituto)
    {
        $delete = new Delete('instituto');
        $delete->where(['idInstituto' => (int) $idInstituto]);
        $this->deleteWith($delete);
    }

    //==========================================================================
    // Para poblar el <select> del formulario
    //==========================================================================
    public function getCiudadesSelect()
    {
        $select = new Select('ciudades');
        $select->columns(['idCiudades', 'ciudad', 'state_province'])
            ->order(['state_province ASC', 'ciudad ASC']);

        $options = [];
        foreach ($this->selectWith($select) as $row) {
            $ciudad  = trim((string) $row['ciudad']);
            $depto   = trim((string) ($row['state_province'] ?? ''));
            $etiqueta = $depto !== ''
                ? "{$ciudad} - {$depto}"
                : $ciudad;
            $options[(int) $row['idCiudades']] = $etiqueta;
        }
        return $options;
    }
}
