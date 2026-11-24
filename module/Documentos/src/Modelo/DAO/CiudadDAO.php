<?php

namespace Documentos\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Expression;
use Documentos\Modelo\Entidades\Ciudad;

class CiudadDAO extends AbstractTableGateway
{
    protected $table = 'ciudades';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //==========================================================================
    public function fetchAll($filtro = '')
    {
        $select = new Select('ciudades');
        $select->columns(['*'])
            ->join(
                ['p' => 'countries'],
                'ciudades.idCountries = p.id',          // ← PK real
                ['pais' => 'pais'],                     // ← columna nombre real
                Select::JOIN_LEFT
            );
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order(['ciudades.idCiudades DESC']);
        }
        return $this->selectWith($select)->toArray();
    }

    public function getCiudad($idCiudades = 0)
    {
        $select = new Select('ciudades');
        $select->where(['idCiudades' => (int) $idCiudades])->limit(1);
        $row = $this->selectWith($select)->current();
        return $row ? new Ciudad($row->getArrayCopy()) : null;
    }

    public function getCiudadDetalle($idCiudades = 0)
    {
        $select = new Select('ciudades');
        $select->columns(['*'])
            ->join(
                ['p' => 'countries'],
                'ciudades.idCountries = p.id',          // ← PK real
                ['pais' => 'pais'],                     // ← columna nombre real
                Select::JOIN_LEFT
            )
            ->where(['ciudades.idCiudades' => (int) $idCiudades])
            ->limit(1);
        $datos = $this->selectWith($select)->toArray();
        return count($datos) > 0 ? $datos[0] : null;
    }

    public function registrar(Ciudad $obj)
    {
        $insert = new Insert('ciudades');
        $datos = $obj->getArrayCopy();
        unset($datos['idCiudades']);
        if (isset($datos['state_province']) && $datos['state_province'] === '') {
            $datos['state_province'] = null;
        }
        $insert->values($datos);
        $this->insertWith($insert);
    }

    public function editar(Ciudad $obj)
    {
        $id = (int) $obj->getIdCiudades();
        $update = new Update('ciudades');
        $datos = $obj->getArrayCopy();
        unset($datos['idCiudades']);
        if (isset($datos['state_province']) && $datos['state_province'] === '') {
            $datos['state_province'] = null;
        }
        $update->set($datos);
        $update->where(['idCiudades' => $id]);
        $this->updateWith($update);
    }

    public function eliminarActivar(Ciudad $obj, $estado = '')
    {
        $update = new Update('ciudades');
        $update->set([
            'estado'        => $estado,
            'modificadopor' => $obj->getModificadopor(),
            'fechahoramod'  => $obj->getFechahoramod(),
        ]);
        $update->where(['idCiudades' => (int) $obj->getIdCiudades()]);
        $this->updateWith($update);
    }

    public function borrarCiudad($idCiudades)
    {
        $delete = new Delete('ciudades');
        $delete->where(['idCiudades' => (int) $idCiudades]);
        $this->deleteWith($delete);
    }

    //==========================================================================
    // Verificar dependencias antes de borrar
    //==========================================================================
    public function tieneInstitutos($idCiudades)
    {
        $select = new Select('instituto');
        $select->columns(['total' => new Expression('COUNT(*)')])
            ->where(['idCiudades' => (int) $idCiudades]);
        $row = $this->selectWith($select)->current();
        return $row && (int) $row['total'] > 0;
    }

    //==========================================================================
    // Para poblar el <select> de países en el formulario
    //==========================================================================
    public function getCountriesSelect()
    {
        $select = new Select('countries');
        $select->columns(['id', 'pais'])->order('pais ASC');
        $options = [];
        foreach ($this->selectWith($select) as $row) {
            $options[(int) $row['id']] = $row['pais'];
        }
        return $options;
    }
}
