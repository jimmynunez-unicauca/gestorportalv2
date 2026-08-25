<?php

namespace Formularios\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;

class UnicaucavirtualDAO extends AbstractTableGateway
{

    protected $table = 'form_unicauca_virtual';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'form_unicauca_virtual';
        $select = new Select($this->table);
        $select->columns(['*'])
            ->join(
                'municipios',
                'form_unicauca_virtual.idMunicipio = municipios.idMunicipio',
                ['municipio']
            )
            ->join(
                'departamentos',
                'departamentos.idDepartamento = municipios.idDepartamento',
                ['departamento']
            )
            ->join(
                'posgrados',
                'posgrados.idPosgrado = form_unicauca_virtual.idPosgrado',
                ['posgrado'],
                Select::JOIN_LEFT
            )
            ->join(
                'programas', // o 'pregrados' según tu tabla real
                'programas.idPrograma = form_unicauca_virtual.idPregrado',
                ['programa'], // alias para el nombre
                Select::JOIN_LEFT
            );

        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("form_unicauca_virtual.idForm DESC");
        }

        return $this->selectWith($select)->toArray();
    }

    public function getFormDetalle($id = 0)
    {
        $select = new Select('form_unicauca_virtual');
        $select->columns(['*'])
            ->where("form_unicauca_virtual.idForm = $id")
            ->join(
                'municipios',
                'form_unicauca_virtual.idMunicipio = municipios.idMunicipio',
                ['municipio']
            )
            ->join(
                'departamentos',
                'departamentos.idDepartamento = municipios.idDepartamento',
                ['departamento']
            )
            ->join(
                'posgrados',
                'posgrados.idPosgrado = form_unicauca_virtual.idPosgrado',
                ['posgrado'],
                Select::JOIN_LEFT
            )   // ← LEFT JOIN
            ->join(
                'programas',
                'programas.idPrograma = form_unicauca_virtual.idPregrado',
                ['programa'],
                Select::JOIN_LEFT
            )   // ← LEFT JOIN
            ->limit(1);

        $datos = $this->selectWith($select)->toArray();
        return count($datos) > 0 ? $datos[0] : null;
    }
    //------------------------------------------------------------------------------

}
